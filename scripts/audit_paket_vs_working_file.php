<?php

/**
 * Read-only audit: compare "Working File PPAB Participants.xlsx"
 * (COMPILED REKAP PESERTA PENDIDIK sheet) against civitas_pendidikans.paket.
 *
 * Matching rule per row:
 *   Registrasi Melalui = WEB PPAB    -> match Nama against ppab_member.name   (id_member -> civitas source_type=table_ppab_baru)
 *   Registrasi Melalui = Google Form -> match Nama against member.member_name (id        -> civitas source_type=table_member_lama)
 * Name match: case-insensitive, trimmed, exact (no LIKE/substring).
 * Paket compare: case-insensitive, trimmed. DB paket convention stays lowercase.
 *
 * No writes. Safe to run repeatedly.
 *
 * Usage: php scripts/audit_paket_vs_working_file.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

$excelPath = '/www/wwwroot/e-sii.yiscalazhar.web.id/e-sii/Working File PPAB Participants.xlsx';
$sheetName = 'COMPILED REKAP PESERTA PENDIDIK';

if (!file_exists($excelPath)) {
    fwrite(STDERR, "File not found: {$excelPath}\n");
    exit(1);
}

function norm(?string $v): string
{
    return strtolower(trim((string) $v));
}

echo "Loading {$excelPath} ...\n";
$spreadsheet = IOFactory::load($excelPath);
$sheet = $spreadsheet->getSheetByName($sheetName);
if (!$sheet) {
    fwrite(STDERR, "Sheet not found: {$sheetName}\n");
    exit(1);
}

$highestRow = $sheet->getHighestDataRow();

// ── Preload lookup maps (avoid N queries per row) ──

echo "Preloading ppab_member ...\n";
$ppabMap = [];
foreach (DB::connection('ppab')->table('ppab_member')->select('id_member', 'name')->get() as $row) {
    $key = norm($row->name);
    if ($key === '') continue;
    $ppabMap[$key][] = $row;
}

echo "Preloading member (lama) ...\n";
$lamaMap = [];
foreach (DB::connection('yisic_db_lama')->table('member')->select('id', 'member_name')->get() as $row) {
    $key = norm($row->member_name);
    if ($key === '') continue;
    $lamaMap[$key][] = $row;
}

echo "Preloading civitas_pendidikans ...\n";
$civitasMap = [];
foreach (DB::table('civitas_pendidikans')->select('source_type', 'source_id', 'paket')->get() as $row) {
    $civitasMap[$row->source_type . '|' . (string) $row->source_id] = $row->paket;
}

// ── Walk the sheet ──

$summary = [];
$details = [];
$tableNotes = [];
$rowsSeen = 0;

for ($r = 2; $r <= $highestRow; $r++) {
    $nama = trim((string) $sheet->getCell("B{$r}")->getValue());
    if ($nama === '') continue; // skip fully blank trailing rows

    $rowsSeen++;
    $no = $sheet->getCell("A{$r}")->getValue();
    $jenisPendidikan = trim((string) $sheet->getCell("C{$r}")->getValue());
    $angkatanBaruLama = trim((string) $sheet->getCell("D{$r}")->getValue());
    $registrasiMelalui = trim((string) $sheet->getCell("E{$r}")->getValue());

    $namaKey = norm($nama);
    $regKey = norm($registrasiMelalui);

    // Prioritas: selalu cek ppab_member (member baru) dulu untuk SEMUA baris,
    // baru fallback ke member (lama) kalau tidak ketemu. Registrasi Melalui
    // (WEB PPAB / Google Form) TIDAK dipakai lagi untuk memilih tabel — hanya
    // dipakai untuk catatan cross-check informasional (lihat di bawah).
    $expectedTable = $regKey === norm('WEB PPAB')
        ? 'ppab_member'
        : ($regKey === norm('Google Form') ? 'member_lama' : null);

    if (!empty($ppabMap[$namaKey] ?? [])) {
        $sourceType = 'table_ppab_baru';
        $candidates = $ppabMap[$namaKey];
        $idField = 'id_member';
        $matchedTable = 'ppab_member';
    } elseif (!empty($lamaMap[$namaKey] ?? [])) {
        $sourceType = 'table_member_lama';
        $candidates = $lamaMap[$namaKey];
        $idField = 'id';
        $matchedTable = 'member_lama';
    } else {
        $sourceType = null;
        $candidates = [];
        $idField = null;
        $matchedTable = null;
    }

    $crossCheckNote = '';
    if ($matchedTable !== null && $expectedTable !== null && $matchedTable !== $expectedTable) {
        $matchedLabel = $matchedTable === 'ppab_member' ? 'ppab_member (member baru)' : 'member (lama)';
        $crossCheckNote .= "Catatan: ditemukan di {$matchedLabel}, meski Registrasi Melalui='{$registrasiMelalui}'. ";
    }
    if ($angkatanBaruLama !== '' && $matchedTable !== null) {
        $wantsLama = str_contains(strtolower($angkatanBaruLama), 'lama');
        $wantsBaru = str_contains(strtolower($angkatanBaruLama), 'baru');
        if (($matchedTable === 'ppab_member' && $wantsLama) || ($matchedTable === 'member_lama' && $wantsBaru)) {
            $crossCheckNote .= "Cross-check: kolom 'Angkatan Baru atau Lama' ('{$angkatanBaruLama}') tampak bertentangan dengan tabel yang match. ";
        }
    }

    if (count($candidates) === 0) {
        $status = 'NOT_FOUND_IN_MEMBER_TABLE';
        $details[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan', 'status') + [
            'civitasPaket' => null, 'candidateId' => null,
            'note' => $crossCheckNote . "Nama tidak ditemukan di ppab_member maupun member (lama)",
        ];
        $summary[$status] = ($summary[$status] ?? 0) + 1;
        continue;
    }

    if (count($candidates) > 1) {
        $status = 'AMBIGUOUS_NAME';
        $candidateInfo = [];
        foreach ($candidates as $c) {
            $id = $c->$idField;
            $paket = $civitasMap[$sourceType . '|' . (string) $id] ?? null;
            $candidateInfo[] = "{$idField}={$id} civitas_paket=" . ($paket ?? 'NULL');
        }
        $details[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan', 'status') + [
            'civitasPaket' => null, 'candidateId' => null,
            'note' => $crossCheckNote . count($candidates) . ' kandidat: ' . implode(' | ', $candidateInfo),
        ];
        $summary[$status] = ($summary[$status] ?? 0) + 1;
        continue;
    }

    // Exactly one candidate
    $candidate = $candidates[0];
    $id = $candidate->$idField;

    if ($id === null || $id === '') {
        $status = 'NULL_IDENTIFIER';
        $details[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan', 'status') + [
            'civitasPaket' => null, 'candidateId' => null,
            'note' => $crossCheckNote . "{$idField} kosong/NULL di tabel member, tidak bisa dicek ke civitas_pendidikans",
        ];
        $summary[$status] = ($summary[$status] ?? 0) + 1;
        continue;
    }

    $civitasKey = $sourceType . '|' . (string) $id;
    if (!array_key_exists($civitasKey, $civitasMap)) {
        $status = 'NO_CIVITAS_RECORD';
        $details[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan', 'status') + [
            'civitasPaket' => null, 'candidateId' => $id,
            'note' => $crossCheckNote . "Tidak ada baris civitas_pendidikans untuk {$sourceType}/{$id}",
        ];
        $summary[$status] = ($summary[$status] ?? 0) + 1;
        continue;
    }

    $civitasPaket = $civitasMap[$civitasKey];
    $match = norm($civitasPaket) === norm($jenisPendidikan);
    $status = $match ? 'MATCH' : 'MISMATCH';

    if (!$match) {
        $details[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan', 'status') + [
            'civitasPaket' => $civitasPaket, 'candidateId' => $id,
            'note' => $crossCheckNote . "xlsx='{$jenisPendidikan}' vs civitas='{$civitasPaket}'",
        ];
    } elseif ($crossCheckNote !== '') {
        // Paket cocok, tapi ditemukan di tabel berbeda dari yang diharapkan Registrasi Melalui.
        $tableNotes[] = compact('no', 'nama', 'registrasiMelalui', 'jenisPendidikan') + [
            'candidateId' => $id, 'note' => trim($crossCheckNote),
        ];
    }
    $summary[$status] = ($summary[$status] ?? 0) + 1;
}

// ── Report ──

echo "\n=== SUMMARY ({$rowsSeen} baris diproses) ===\n";
ksort($summary);
foreach ($summary as $status => $count) {
    echo str_pad($status, 30) . ": {$count}\n";
}

echo "\n=== DETAIL (semua baris NON-MATCH) ===\n";
foreach ($details as $d) {
    echo "No={$d['no']} | {$d['nama']} | {$d['registrasiMelalui']} | xlsx_paket={$d['jenisPendidikan']} | civitas_paket=" . ($d['civitasPaket'] ?? '-') . " | id=" . ($d['candidateId'] ?? '-') . " | STATUS={$d['status']}\n";
    echo "    note: {$d['note']}\n";
}

if (!empty($tableNotes)) {
    echo "\n=== INFO: MATCH tapi ditemukan di tabel berbeda dari Registrasi Melalui (" . count($tableNotes) . " baris) ===\n";
    foreach ($tableNotes as $n) {
        echo "No={$n['no']} | {$n['nama']} | {$n['registrasiMelalui']} | id={$n['candidateId']} | {$n['note']}\n";
    }
}

// ── CSV export ──

$csvPath = __DIR__ . '/../storage/app/audit_paket_vs_working_file_' . date('Ymd_His') . '.csv';
$fh = fopen($csvPath, 'w');
fputcsv($fh, ['No', 'Nama', 'Registrasi Melalui', 'Jenis Pendidikan (xlsx)', 'Paket (civitas)', 'Candidate ID', 'Status', 'Note']);
foreach ($details as $d) {
    fputcsv($fh, [$d['no'], $d['nama'], $d['registrasiMelalui'], $d['jenisPendidikan'], $d['civitasPaket'] ?? '', $d['candidateId'] ?? '', $d['status'], $d['note']]);
}
foreach ($tableNotes as $n) {
    fputcsv($fh, [$n['no'], $n['nama'], $n['registrasiMelalui'], '', '', $n['candidateId'], 'MATCH_TABLE_NOTE', $n['note']]);
}
fclose($fh);

echo "\nCSV disimpan di: {$csvPath}\n";
