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
        Schema::create('mutabaah_qurans', function (Blueprint $table) {
            $table->id();
            $table->string('civitas_id')->index();
            $table->date('pertama_setor');
            $table->string('from_surah');
            $table->integer('from_ayat');
            $table->string('to_surah');
            $table->integer('to_ayat');
            $table->integer('total_halaman');
            $table->decimal('total_juz', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutabaah_qurans');
    }
};