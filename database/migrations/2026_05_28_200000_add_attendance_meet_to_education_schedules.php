<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('education_schedules', 'attendance_mode')) {
                $table->string('attendance_mode')->default('offline')->after('type');
            }
            if (!Schema::hasColumn('education_schedules', 'meeting_link')) {
                $table->string('meeting_link')->nullable()->after('attendance_mode');
            }
            if (!Schema::hasColumn('education_schedules', 'google_event_id')) {
                $table->string('google_event_id')->nullable()->after('meeting_link');
            }
            if (!Schema::hasColumn('education_schedules', 'google_space_name')) {
                $table->string('google_space_name')->nullable()->after('google_event_id');
            }
            if (!Schema::hasColumn('education_schedules', 'meet_access_type')) {
                $table->string('meet_access_type')->default('OPEN')->after('google_space_name');
            }
            if (!Schema::hasColumn('education_schedules', 'meet_entry_point_access')) {
                $table->string('meet_entry_point_access')->default('ALL')->after('meet_access_type');
            }
            if (!Schema::hasColumn('education_schedules', 'meet_moderation')) {
                $table->string('meet_moderation')->default('OFF')->after('meet_entry_point_access');
            }
            if (!Schema::hasColumn('education_schedules', 'meet_description')) {
                $table->text('meet_description')->nullable()->after('meet_moderation');
            }
            if (!Schema::hasColumn('education_schedules', 'meet_co_host_email')) {
                $table->string('meet_co_host_email')->nullable()->after('meet_description');
            }
            if (!Schema::hasColumn('education_schedules', 'send_reminder')) {
                $table->boolean('send_reminder')->default(false)->after('meet_co_host_email');
            }
            if (!Schema::hasColumn('education_schedules', 'reminder_before')) {
                $table->string('reminder_before')->nullable()->after('send_reminder'); // format HH:MM
            }
            if (!Schema::hasColumn('education_schedules', 'reminder_sent')) {
                $table->boolean('reminder_sent')->default(false)->after('reminder_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('education_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_mode',
                'meeting_link',
                'google_event_id',
                'google_space_name',
                'meet_access_type',
                'meet_entry_point_access',
                'meet_moderation',
                'meet_description',
                'meet_co_host_email',
                'send_reminder',
                'reminder_before',
                'reminder_sent',
            ]);
        });
    }
};
