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
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'photo')) {
                $table->string('photo')->nullable()->after('id');
            }
            if (!Schema::hasColumn('teachers', 'name')) {
                $table->string('name')->after('photo');
            }
            if (!Schema::hasColumn('teachers', 'education_history')) {
                $table->text('education_history')->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['photo', 'name', 'education_history']);
        });
    }
};
