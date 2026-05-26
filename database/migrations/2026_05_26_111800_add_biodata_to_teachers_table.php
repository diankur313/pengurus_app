<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('name');
            }
            if (!Schema::hasColumn('teachers', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('teachers', 'gender')) {
                $table->enum('gender', ['pria', 'wanita'])->nullable()->after('tanggal_lahir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'tanggal_lahir', 'gender']);
        });
    }
};
