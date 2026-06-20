<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XenditWebhookLog extends Model
{
    protected $table = 'xendit_webhook_logs';

    protected $fillable = [
        'external_id',
        'app_id',
        'app_name',
        'status',
        'payment_method',
        'bank_code',
        'amount',
        'fee_pg',
        'fee_sysdev',
        'withdrawable',
        'forward_url',
        'forward_status',
        'forward_response',
        'raw_payload',
        'paid_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'paid_at'     => 'datetime',
        'amount'      => 'integer',
        'fee_pg'      => 'integer',
        'fee_sysdev'  => 'integer',
        'withdrawable'=> 'integer',
    ];

    public function isForwardSuccess(): bool
    {
        return in_array($this->forward_status, [200, 201]);
    }
}
