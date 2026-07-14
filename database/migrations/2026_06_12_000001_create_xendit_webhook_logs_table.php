<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gunakan koneksi 'ppab' sesuai dengan model XenditWebhookLog
        Schema::connection('ppab')->create('xendit_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();
            $table->string('app_id')->index()->nullable()->comment('ID aplikasi klien, misal: join-ppab, e-yac');
            $table->string('app_name')->nullable()->comment('Nama aplikasi dari paymentgatewayfees');
            $table->string('status')->index()->comment('PAID, SETTLED, EXPIRED, dll');
            $table->string('payment_method')->nullable()->comment('BANK_TRANSFER, QR_CODE, RETAIL_OUTLET');
            $table->string('bank_code')->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('fee_pg')->default(0)->comment('Fee payment gateway (Xendit)');
            $table->unsignedBigInteger('fee_sysdev')->default(0)->comment('Fee sysdev dari config');
            $table->unsignedBigInteger('withdrawable')->default(0)->comment('Jumlah bersih yang dapat dicairkan');
            $table->string('forward_url')->nullable()->comment('URL child app yang dituju');
            $table->tinyInteger('forward_status')->nullable()->comment('HTTP status code hasil forward ke child app');
            $table->text('forward_response')->nullable()->comment('Response body dari child app');
            $table->json('raw_payload')->nullable()->comment('Payload asli dari Xendit');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('ppab')->dropIfExists('xendit_webhook_logs');
    }
};
