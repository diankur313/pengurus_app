<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('amount_dasar', 'semester_2');
            $table->renameColumn('amount_lanjutan', 'semester_3');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('semester_2', 'amount_dasar');
            $table->renameColumn('semester_3', 'amount_lanjutan');
        });
    }
};
