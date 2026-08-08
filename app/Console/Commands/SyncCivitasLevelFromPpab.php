<?php

namespace App\Console\Commands;

use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;
use Illuminate\Console\Command;

class SyncCivitasLevelFromPpab extends Command
{
    protected $signature = 'civitas:sync-level';
    protected $description = 'Sinkronkan level_angkatan & paket di civitas_pendidikans dari ppab_member';

    public function handle()
    {
        $this->info('Mulai sinkronisasi level_angkatan & paket dari ppab_member...');

        $civitasRecords = CivitasPendidikan::where('source_type', 'table_ppab_baru')
            ->whereNotNull('source_id')
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($civitasRecords as $civitas) {
            // source_id stores MemberPpab's unique auto-increment id, not id_member
            // (id_member is not unique across ppab_member rows).
            $member = MemberPpab::where('id', $civitas->source_id)->first();

            if (!$member) {
                $skipped++;
                continue;
            }

            // Ambil level_angkatan & paket dari ppab_member
            $ppabLevel = $member->level_angkatan;
            $ppabPaket = $member->paket;

            $rawLevel = $civitas->getRawOriginal('level_angkatan');
            $rawPaket = $civitas->getRawOriginal('paket');

            // Jika ada perbedaan, update
            if ($rawLevel !== $ppabLevel || $rawPaket !== $ppabPaket) {
                $civitas->update([
                    'level_angkatan' => $ppabLevel,
                    'paket' => $ppabPaket,
                ]);
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->info("Selesai!");
        $this->info("Updated: {$updated} record(s)");
        $this->info("Skipped: {$skipped} record(s)");

        return Command::SUCCESS;
    }
}
