<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection("ppab")->table("ppab_sessions", function (Blueprint $table) {
            $table->unsignedTinyInteger("age_limit")->nullable()->after("session_date_end");
        });
    }

    public function down(): void
    {
        Schema::connection("ppab")->table("ppab_sessions", function (Blueprint $table) {
            $table->dropColumn("age_limit");
        });
    }
};
