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
        Schema::create('civitas_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source_type'); // 'table_ppab_baru' or 'table_member_lama'
            $table->string('source_id');
            $table->string('level_angkatan')->nullable(); // 'Angdas', 'Lanjutan', 'Pasca'
            $table->timestamps();
            
            // Add index for faster searching
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('civitas_pendidikans');
    }
};
