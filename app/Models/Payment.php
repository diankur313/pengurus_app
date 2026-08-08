<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PaymentGatewayFee;

class Payment extends Model
{
    protected $fillable = [
        // Info Penagihan
        'desc', 'start', 'end', 'level',
        'va', 'qris', 'cs',
        'semester_2', 'semester_3', 'payment_method',
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
        'semester_2'           => 'decimal:2',
        'semester_3'           => 'decimal:2',
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

        // Auto-fill fee_sysdev based on id_apps
        static::creating(function (self $model) {
            if (!empty($model->id_apps) && empty($model->fee_sysdev)) {
                $feeRule = PaymentGatewayFee::where('app_id', $model->id_apps)->first();
                if ($feeRule) {
                    $model->fee_sysdev = $feeRule->sysdev_fee;
                }
            }
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
