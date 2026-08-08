<?php

namespace App\Observers;

use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;

class CivitasPendidikanObserver
{
    public function updated(CivitasPendidikan $civitas)
    {
        if ($civitas->source_type !== 'table_ppab_baru' || !$civitas->source_id) {
            return;
        }

        $changes = [];

        if ($civitas->wasChanged('paket')) {
            $changes['paket'] = $civitas->paket;
        }

        if ($civitas->wasChanged('level_angkatan')) {
            $changes['level_angkatan'] = (int) str_replace('semester_', '', $civitas->level_angkatan);
        }

        if (!empty($changes)) {
            // source_id stores MemberPpab's unique auto-increment id, not id_member
            // (id_member is not unique across ppab_member rows).
            MemberPpab::where('id', $civitas->source_id)->update($changes);
        }
    }
}
