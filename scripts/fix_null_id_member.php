<?php

/**
 * One-time fix: backfill ppab_member.id_member for ONE specific member
 * stuck with stage='paid_payment' but id_member NULL/empty (webhook never
 * assigned one, e.g. because payment was confirmed manually instead of
 * via Xendit).
 *
 * Scoped ONLY to soulisaniarimbi@gmail.com — not a generic bulk fix.
 *
 * Reproduces the exact numbering scheme used by the real Xendit webhook
 * (IdAngdas() in /www/wwwroot/ppab.yiscalazhar.web.id/frontend/app/Helper.php):
 *   tahun(2) . bulan(2) . urutan(3, count of other paid_payment members
 *   in the same id_session, +1)
 *
 * Usage:
 *   php scripts/fix_null_id_member.php            # dry-run, no writes
 *   php scripts/fix_null_id_member.php --apply     # perform the update
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);

echo "=== Fix ppab_member.id_member for stuck paid_payment rows ===\n";
echo $apply ? "MODE: APPLY (writes will be committed)\n\n" : "MODE: DRY-RUN (no writes, use --apply to commit)\n\n";

$targetEmail = 'soulisaniarimbi@gmail.com';

$candidates = DB::connection('ppab')->table('ppab_member')
    ->where('email', $targetEmail)
    ->where('stage', 'paid_payment')
    ->where(function ($q) {
        $q->whereNull('id_member')->orWhere('id_member', '');
    })
    ->get(['id', 'name', 'email', 'id_session']);

echo "Target: {$targetEmail}\n";
echo "Kandidat ditemukan: {$candidates->count()}\n\n";

if ($candidates->isEmpty()) {
    echo "Tidak ada yang perlu diperbaiki (mungkin sudah diperbaiki sebelumnya, atau email/kondisi tidak cocok).\n";
    exit(0);
}

$tahun = date('y');
$bulan = date('m');
$plan = [];

foreach ($candidates as $c) {
    $jumlah = DB::connection('ppab')->table('ppab_member')
        ->where('id_session', $c->id_session)
        ->where('stage', 'paid_payment')
        ->where('id', '!=', $c->id)
        ->count();

    $newIdMember = $tahun . $bulan . str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT);

    $conflict = DB::connection('ppab')->table('ppab_member')
        ->where('id_member', $newIdMember)
        ->exists();

    $plan[] = [
        'id' => $c->id,
        'name' => $c->name,
        'email' => $c->email,
        'id_session' => $c->id_session,
        'new_id_member' => $newIdMember,
        'conflict' => $conflict,
    ];
}

foreach ($plan as $p) {
    $flag = $p['conflict'] ? ' [CONFLICT - SKIPPED]' : '';
    echo "id={$p['id']} name={$p['name']} email={$p['email']} id_session={$p['id_session']} -> new id_member={$p['new_id_member']}{$flag}\n";
}

if (!$apply) {
    echo "\nDry-run complete. Re-run with --apply to commit these changes.\n";
    exit(0);
}

echo "\nApplying updates...\n";
$updated = 0;
$skipped = 0;
foreach ($plan as $p) {
    if ($p['conflict']) {
        $skipped++;
        continue;
    }
    DB::connection('ppab')->table('ppab_member')
        ->where('id', $p['id'])
        ->update(['id_member' => $p['new_id_member']]);
    $updated++;
}

echo "Done. Updated {$updated} record(s), skipped {$skipped} due to conflict.\n";

$remaining = DB::connection('ppab')->table('ppab_member')
    ->where('stage', 'paid_payment')
    ->where(function ($q) {
        $q->whereNull('id_member')->orWhere('id_member', '');
    })
    ->count();

echo "Verifikasi: sisa kandidat dengan id_member kosong = {$remaining}\n";
