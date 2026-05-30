<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscountCoupon extends Model
{
    protected $fillable = [
        'code', 'name', 'amount',
        'valid_from', 'valid_until',
        'used_by', 'used_at',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'used_at'    => 'datetime',
        'is_active'  => 'boolean',
        'amount'     => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            // Auto-generate unique 6-char uppercase code
            do {
                $code = strtoupper(Str::random(6));
            } while (static::where('code', $code)->exists());

            $model->code = $code;
        });
    }

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentLog::class, 'coupon_id');
    }
}
