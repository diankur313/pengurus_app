<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayFee extends Model
{
    use HasFactory;

    protected $connection = 'ppab';
    protected $table = 'paymentgatewayfees';

    protected $fillable = [
        'app_name',
        'app_id',
        'mode',
        'internal_webhook_url',
        'sysdev_fee',
        'va_fee',
        'qr_fee',
        'outlet_fee',
        'ppn',
    ];

    protected $casts = [
        'sysdev_fee' => 'decimal:2',
        'va_fee'     => 'decimal:2',
        'qr_fee'     => 'decimal:2',
        'outlet_fee' => 'decimal:2',
        'ppn'        => 'decimal:2',
    ];

    public function isProduction(): bool
    {
        return $this->mode === 'production';
    }
}
