<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('ppab')->table('ppab_sessions', function (Blueprint $table) {
            // Rename existing to sii_
            $table->renameColumn('quota_full', 'sii_quota_full');
            $table->renameColumn('price_full', 'sii_price_full');
            $table->renameColumn('id_full', 'sii_id_full');
            
            $table->renameColumn('quota_dp', 'sii_quota_dp');
            $table->renameColumn('price_dp', 'sii_price_dp');
            $table->renameColumn('id_dp', 'sii_id_dp');
            
            $table->renameColumn('quota_early_bird', 'sii_quota_early_bird');
            $table->renameColumn('price_early_bird', 'sii_price_early_bird');
            $table->renameColumn('id_early_bird', 'sii_id_early_bird');
            
            $table->renameColumn('bundling_2', 'sii_bundling_2');
            $table->renameColumn('id_bundling_2', 'sii_id_bundling_2');
            
            $table->renameColumn('bundling_3', 'sii_bundling_3');
            $table->renameColumn('id_bundling_3', 'sii_id_bundling_3');

            // Add BSQ columns
            $table->integer('bsq_quota_full')->nullable();
            $table->integer('bsq_price_full')->nullable();
            $table->string('bsq_id_full')->nullable();

            $table->integer('bsq_quota_dp')->nullable();
            $table->integer('bsq_price_dp')->nullable();
            $table->string('bsq_id_dp')->nullable();

            $table->integer('bsq_quota_early_bird')->nullable();
            $table->integer('bsq_price_early_bird')->nullable();
            $table->string('bsq_id_early_bird')->nullable();

            $table->integer('bsq_bundling_2')->nullable();
            $table->string('bsq_id_bundling_2')->nullable();

            $table->integer('bsq_bundling_3')->nullable();
            $table->string('bsq_id_bundling_3')->nullable();

            // Add SII + BSQ columns
            $table->integer('sii_bsq_quota_full')->nullable();
            $table->integer('sii_bsq_price_full')->nullable();
            $table->string('sii_bsq_id_full')->nullable();

            $table->integer('sii_bsq_quota_dp')->nullable();
            $table->integer('sii_bsq_price_dp')->nullable();
            $table->string('sii_bsq_id_dp')->nullable();

            $table->integer('sii_bsq_quota_early_bird')->nullable();
            $table->integer('sii_bsq_price_early_bird')->nullable();
            $table->string('sii_bsq_id_early_bird')->nullable();

            $table->integer('sii_bsq_bundling_2')->nullable();
            $table->string('sii_bsq_id_bundling_2')->nullable();

            $table->integer('sii_bsq_bundling_3')->nullable();
            $table->string('sii_bsq_id_bundling_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ppab')->table('ppab_sessions', function (Blueprint $table) {
            // Drop SII + BSQ columns
            $table->dropColumn([
                'sii_bsq_quota_full', 'sii_bsq_price_full', 'sii_bsq_id_full',
                'sii_bsq_quota_dp', 'sii_bsq_price_dp', 'sii_bsq_id_dp',
                'sii_bsq_quota_early_bird', 'sii_bsq_price_early_bird', 'sii_bsq_id_early_bird',
                'sii_bsq_bundling_2', 'sii_bsq_id_bundling_2',
                'sii_bsq_bundling_3', 'sii_bsq_id_bundling_3'
            ]);

            // Drop BSQ columns
            $table->dropColumn([
                'bsq_quota_full', 'bsq_price_full', 'bsq_id_full',
                'bsq_quota_dp', 'bsq_price_dp', 'bsq_id_dp',
                'bsq_quota_early_bird', 'bsq_price_early_bird', 'bsq_id_early_bird',
                'bsq_bundling_2', 'bsq_id_bundling_2',
                'bsq_bundling_3', 'bsq_id_bundling_3'
            ]);

            // Revert SII columns
            $table->renameColumn('sii_quota_full', 'quota_full');
            $table->renameColumn('sii_price_full', 'price_full');
            $table->renameColumn('sii_id_full', 'id_full');

            $table->renameColumn('sii_quota_dp', 'quota_dp');
            $table->renameColumn('sii_price_dp', 'price_dp');
            $table->renameColumn('sii_id_dp', 'id_dp');

            $table->renameColumn('sii_quota_early_bird', 'quota_early_bird');
            $table->renameColumn('sii_price_early_bird', 'price_early_bird');
            $table->renameColumn('sii_id_early_bird', 'id_early_bird');

            $table->renameColumn('sii_bundling_2', 'bundling_2');
            $table->renameColumn('sii_id_bundling_2', 'id_bundling_2');

            $table->renameColumn('sii_bundling_3', 'bundling_3');
            $table->renameColumn('sii_id_bundling_3', 'id_bundling_3');
        });
    }
};
