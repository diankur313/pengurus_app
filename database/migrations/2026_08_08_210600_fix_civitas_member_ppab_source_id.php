<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix civitas_pendidikans.source_id for table_ppab_baru records.
     *
     * MemberPpab declares id_member as its primary key, but id_member is
     * NOT unique in ppab_member (e.g. id_member 2501002 is shared by both
     * "Ainun Majid" and "Alodia Lalita"), so records resolved to the wrong
     * person. The real unique identifier is the auto-increment "id" column.
     * This migration rewrites source_id from id_member -> id.
     */
    public function up(): void
    {
        $appConnection = config('database.default');

        // Map id_member -> id. For duplicate id_member, prefer the newest row (max id).
        $rows = DB::connection('ppab')
            ->table('ppab_member')
            ->select('id_member', DB::raw('MAX(id) as id'))
            ->whereNotNull('id_member')
            ->where('id_member', '!=', '')
            ->groupBy('id_member')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->id_member] = $row->id;
        }

        $civitas = DB::connection($appConnection)
            ->table('civitas_pendidikans')
            ->where('source_type', 'table_ppab_baru')
            ->get();

        foreach ($civitas as $record) {
            if (!isset($map[$record->source_id])) {
                continue;
            }
            DB::connection($appConnection)
                ->table('civitas_pendidikans')
                ->where('id', $record->id)
                ->update(['source_id' => $map[$record->source_id]]);
        }
    }

    public function down(): void
    {
        // Reversing is not reliable because id_member is non-unique.
    }
};
