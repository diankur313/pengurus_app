<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'payment_id',
        'civitas_id',
        'angkatan',
        'amount',
        'coupon_id',
        'discount_amount',
        'status',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(DiscountCoupon::class, 'coupon_id');
    }

    /**
     * Get civitas data (cross-table via CivitasPendidikan uuid).
     */
    public function civitas(): BelongsTo
    {
        return $this->belongsTo(CivitasPendidikan::class, 'civitas_id', 'uuid');
    }
}
