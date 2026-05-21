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
        Schema::table('attendances', function (Blueprint $table) {
            $table->uuid('civitas_id')->after('id');
            $table->uuid('schedule_id')->after('civitas_id');
            $table->unsignedBigInteger('scanned_by_user_id')->after('schedule_id')->nullable();
            $table->string('status')->default('hadir')->after('scanned_by_user_id');

            // Set up relations
            // Although civitas_pendidikans uses auto-increment id as PK, 
            // the civitas_id here stores the uuid. So we don't use strict foreign key constraints 
            // for civitas_id and schedule_id if they reference uuids (since uuid is just a unique column).
            // Actually, we can just index them for fast lookups.
            $table->index('civitas_id');
            $table->index('schedule_id');

            $table->foreign('scanned_by_user_id')->references('id')->on('users')->nullOnDelete();

            // Prevent double scans
            $table->unique(['civitas_id', 'schedule_id'], 'attendance_unique_scan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['scanned_by_user_id']);
            $table->dropUnique('attendance_unique_scan');
            $table->dropColumn(['civitas_id', 'schedule_id', 'scanned_by_user_id', 'status']);
        });
    }
};
