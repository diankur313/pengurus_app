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
        Schema::table('education_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('education_schedules', 'uuid')) {
                $table->string('uuid', 12)->unique()->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_schedules', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
