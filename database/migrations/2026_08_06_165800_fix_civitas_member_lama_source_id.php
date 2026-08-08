<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix civitas_pendidikans.source_id for table_member_lama records.
     *
     * Previously source_id stored the member_no from the old "member" table,
     * but member_no is NOT unique (165 duplicate values), so records resolved
     * to the wrong person (e.g. Nadya Herbawani -> Buni Pradina Bestari,
     * both share member_no 2190045).
     *
     * The real unique identifier is the auto-increment "id" column.
     * This migration rewrites source_id from member_no -> id.
     */
    public function up(): void
    {
        $memberLamaConnection = config('database.connections.yisic_db_lama.database');
        $appConnection = config('database.default');

        // Map member_no -> id. For duplicate member_no, prefer the newest row (max id).
        $rows = DB::connection('yisic_db_lama')
            ->table('member')
            ->select('member_no', DB::raw('MAX(id) as id'))
            ->whereNotNull('member_no')
            ->where('member_no', '!=', '')
            ->groupBy('member_no')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->member_no] = $row->id;
        }

        $civitas = DB::connection($appConnection)
            ->table('civitas_pendidikans')
            ->where('source_type', 'table_member_lama')
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
        // Reversing is not reliable because member_no is non-unique.
    }
};
