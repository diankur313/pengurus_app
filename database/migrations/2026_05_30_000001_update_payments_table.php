<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // — Info Penagihan —
            $table->string('desc')->after('id');
            $table->date('start')->after('desc');
            $table->date('end')->after('start');
            $table->boolean('va')->default(false)->after('end');
            $table->boolean('qris')->default(false)->after('va');
            $table->boolean('cs')->default(false)->after('qris');
            $table->decimal('amount_dasar', 15, 2)->default(0)->after('cs');
            $table->decimal('amount_lanjutan', 15, 2)->default(0)->after('amount_dasar');
            $table->json('payment_method')->nullable()->after('amount_lanjutan');
            // — Reminder —
            $table->boolean('send_reminder')->default(false)->after('payment_method');
            $table->integer('reminder_days_before')->nullable()->after('send_reminder');
            $table->boolean('reminder_sent')->default(false)->after('reminder_days_before');
            // — Gateway & Finance —
            $table->string('id_apps')->nullable()->after('reminder_sent');
            $table->string('external_id')->nullable()->after('id_apps');
            $table->decimal('amount', 15, 2)->nullable()->after('external_id');
            $table->decimal('disc', 15, 2)->default(0)->after('amount');
            $table->string('refferal', 6)->nullable()->after('disc');
            $table->enum('method', ['va', 'qris', 'cs'])->nullable()->after('refferal');
            $table->string('bank_name')->nullable()->after('method');
            $table->string('status')->default('active')->after('bank_name'); // active/closed/PENDING/PAID/EXPIRED/BYPASS
            $table->dateTime('settle_date')->nullable()->after('status');
            $table->dateTime('expire_at')->nullable()->after('settle_date');
            $table->text('invoice_url')->nullable()->after('expire_at');
            $table->decimal('fee_pg', 15, 2)->default(0)->after('invoice_url');
            $table->decimal('fee_sysdev', 15, 2)->default(0)->after('fee_pg');
            $table->decimal('withdrawable', 15, 2)->default(0)->after('fee_sysdev');
            $table->boolean('withdrawable_ability')->default(false)->after('withdrawable');
            // — Meta —
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('withdrawable_ability');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'desc', 'start', 'end', 'va', 'qris', 'cs',
                'amount_dasar', 'amount_lanjutan', 'payment_method',
                'send_reminder', 'reminder_days_before', 'reminder_sent',
                'id_apps', 'external_id', 'amount', 'disc', 'refferal',
                'method', 'bank_name', 'status', 'settle_date', 'expire_at',
                'invoice_url', 'fee_pg', 'fee_sysdev', 'withdrawable',
                'withdrawable_ability', 'created_by',
            ]);
        });
    }
};
