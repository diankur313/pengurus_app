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
        Schema::table('xendit_webhook_logs', function (Blueprint $table) {
            $table->smallInteger('forward_status')->nullable()->comment('HTTP status code hasil forward ke child app')->change();
        });
    }

    public function down(): void
    {
        Schema::table('xendit_webhook_logs', function (Blueprint $table) {
            $table->tinyInteger('forward_status')->nullable()->comment('HTTP status code hasil forward ke child app')->change();
        });
    }
};
