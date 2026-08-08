<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('civitas_pendidikans', function (Blueprint $table) {
            $table->string('paket')->nullable()->after('level_angkatan');
        });

        // Update existing records
        $civitasList = DB::table('civitas_pendidikans')
            ->where('source_type', 'table_ppab_baru')
            ->get();

        foreach ($civitasList as $civitas) {
            $ppabMember = DB::connection('ppab')
                ->table('ppab_member')
                ->where('id_member', $civitas->source_id)
                ->first();

            if ($ppabMember && !empty($ppabMember->paket)) {
                DB::table('civitas_pendidikans')
                    ->where('id', $civitas->id)
                    ->update(['paket' => $ppabMember->paket]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civitas_pendidikans', function (Blueprint $table) {
            $table->dropColumn('paket');
        });
    }
};