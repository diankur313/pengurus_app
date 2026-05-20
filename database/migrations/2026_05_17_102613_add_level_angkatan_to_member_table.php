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
        Schema::connection('yisic_db_lama')->table('member', function (Blueprint $table) {
            if (!Schema::connection('yisic_db_lama')->hasColumn('member', 'level_angkatan')) {
                $table->string('level_angkatan')->nullable()->after('member_nama_angkatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('yisic_db_lama')->table('member', function (Blueprint $table) {
            if (Schema::connection('yisic_db_lama')->hasColumn('member', 'level_angkatan')) {
                $table->dropColumn('level_angkatan');
            }
        });
    }
};
