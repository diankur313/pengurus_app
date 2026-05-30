<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('used_by')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_coupons');
    }
};
