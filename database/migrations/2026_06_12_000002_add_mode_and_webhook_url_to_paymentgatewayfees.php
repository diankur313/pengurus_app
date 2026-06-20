<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('ppab')->table('paymentgatewayfees', function (Blueprint $table) {
            $table->enum('mode', ['development', 'production'])->default('development')->after('app_id');
            $table->string('internal_webhook_url')->nullable()->after('mode')
                ->comment('URL endpoint internal webhook di child app, contoh: https://join-ppab.../api/internal/webhook/invoice');
        });
    }

    public function down(): void
    {
        Schema::connection('ppab')->table('paymentgatewayfees', function (Blueprint $table) {
            $table->dropColumn(['mode', 'internal_webhook_url']);
        });
    }
};
