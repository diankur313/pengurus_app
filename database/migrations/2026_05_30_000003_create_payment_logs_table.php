<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('civitas_id');
            $table->string('angkatan'); // dasar / lanjutan
            $table->decimal('amount', 15, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('discount_coupons')->onDelete('set null');
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->enum('status', ['PENDING', 'PAID'])->default('PENDING');
            $table->timestamps();

            $table->index(['payment_id']);
            $table->index(['civitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
