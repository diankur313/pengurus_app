<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        // Info Penagihan
        'desc', 'start', 'end',
        'va', 'qris', 'cs',
        'amount_dasar', 'amount_lanjutan', 'payment_method',
        // Reminder
        'send_reminder', 'reminder_days_before', 'reminder_sent',
        // Gateway & Finance
        'id_apps', 'external_id', 'amount', 'disc', 'refferal',
        'method', 'bank_name', 'status',
        'settle_date', 'expire_at', 'invoice_url',
        'fee_pg', 'fee_sysdev',
        'withdrawable', 'withdrawable_ability',
        // Meta
        'created_by',
    ];

    protected $casts = [
        'start'                => 'date',
        'end'                  => 'date',
        'va'                   => 'boolean',
        'qris'                 => 'boolean',
        'cs'                   => 'boolean',
        'amount_dasar'         => 'decimal:2',
        'amount_lanjutan'      => 'decimal:2',
        'payment_method'       => 'array',
        'send_reminder'        => 'boolean',
        'reminder_sent'        => 'boolean',
        'amount'               => 'decimal:2',
        'disc'                 => 'decimal:2',
        'settle_date'          => 'datetime',
        'expire_at'            => 'datetime',
        'fee_pg'               => 'decimal:2',
        'fee_sysdev'           => 'decimal:2',
        'withdrawable'         => 'decimal:2',
        'withdrawable_ability' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Auto-populate payment_method array from va/qris/cs flags
        static::saving(function (self $model) {
            $methods = [];
            if ($model->va)   $methods = array_merge($methods, ['BNI', 'BSI', 'BRI', 'MANDIRI', 'PERMATA']);
            if ($model->qris) $methods[] = 'QRIS';
            if ($model->cs)   $methods[] = 'INDOMARET';
            $model->payment_method = $methods;
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }
}
