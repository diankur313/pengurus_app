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
            if (!Schema::hasColumn('education_schedules', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('education_schedules', 'type')) {
                $table->string('type')->after('teacher_id');
            }
            if (!Schema::hasColumn('education_schedules', 'title')) {
                $table->string('title')->after('type');
            }
            if (!Schema::hasColumn('education_schedules', 'level')) {
                $table->string('level')->after('title');
            }
            if (!Schema::hasColumn('education_schedules', 'start_at')) {
                $table->dateTime('start_at')->after('level');
            }
            if (!Schema::hasColumn('education_schedules', 'end_at')) {
                $table->dateTime('end_at')->after('start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'type', 'title', 'level', 'start_at', 'end_at']);
        });
    }
};
