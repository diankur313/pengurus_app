<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->default('android'); // android / ios
            $table->string('version_name'); // "2.0.0"
            $table->unsignedInteger('version_code'); // harus sama dengan versionCode di build.gradle
            $table->boolean('is_mandatory')->default(false); // "paten" / wajib
            $table->text('custom_message')->nullable();
            $table->string('download_url')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'version_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
