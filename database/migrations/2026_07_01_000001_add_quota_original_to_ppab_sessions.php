<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah kolom *_quota_*_original ke ppab_sessions.
     * Kolom ini adalah snapshot immutable quota yang di-set admin.
     * Kolom live (*_quota_full dsb.) tetap dipakai sebagai availability counter.
     */
    public function up(): void
    {
        Schema::connection('ppab')->table('ppab_sessions', function (Blueprint $table) {
            // SII
            $table->unsignedInteger('sii_quota_full_original')->nullable()->after('sii_quota_full');
            $table->unsignedInteger('sii_quota_dp_original')->nullable()->after('sii_quota_dp');
            $table->unsignedInteger('sii_quota_early_bird_original')->nullable()->after('sii_quota_early_bird');

            // BSQ
            $table->unsignedInteger('bsq_quota_full_original')->nullable()->after('bsq_quota_full');
            $table->unsignedInteger('bsq_quota_dp_original')->nullable()->after('bsq_quota_dp');
            $table->unsignedInteger('bsq_quota_early_bird_original')->nullable()->after('bsq_quota_early_bird');

            // SII + BSQ
            $table->unsignedInteger('sii_bsq_quota_full_original')->nullable()->after('sii_bsq_quota_full');
            $table->unsignedInteger('sii_bsq_quota_dp_original')->nullable()->after('sii_bsq_quota_dp');
            $table->unsignedInteger('sii_bsq_quota_early_bird_original')->nullable()->after('sii_bsq_quota_early_bird');
        });

        // Populate existing rows: original = live (nilai terbaik yang kita tahu saat ini)
        DB::connection('ppab')->table('ppab_sessions')->update([
            'sii_quota_full_original'     => DB::raw('sii_quota_full'),
            'sii_quota_dp_original'       => DB::raw('sii_quota_dp'),
            'sii_quota_early_bird_original' => DB::raw('sii_quota_early_bird'),
            'bsq_quota_full_original'     => DB::raw('bsq_quota_full'),
            'bsq_quota_dp_original'       => DB::raw('bsq_quota_dp'),
            'bsq_quota_early_bird_original' => DB::raw('bsq_quota_early_bird'),
            'sii_bsq_quota_full_original' => DB::raw('sii_bsq_quota_full'),
            'sii_bsq_quota_dp_original'   => DB::raw('sii_bsq_quota_dp'),
            'sii_bsq_quota_early_bird_original' => DB::raw('sii_bsq_quota_early_bird'),
        ]);
    }

    public function down(): void
    {
        Schema::connection('ppab')->table('ppab_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'sii_quota_full_original',
                'sii_quota_dp_original',
                'sii_quota_early_bird_original',
                'bsq_quota_full_original',
                'bsq_quota_dp_original',
                'bsq_quota_early_bird_original',
                'sii_bsq_quota_full_original',
                'sii_bsq_quota_dp_original',
                'sii_bsq_quota_early_bird_original',
            ]);
        });
    }
};
