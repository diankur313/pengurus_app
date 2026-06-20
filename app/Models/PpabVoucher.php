<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpabVoucher extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_vouchers';

    protected $guarded = [];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * Define the relationship to the User model (from join-ppab).
     * Since the User model might be in the main app, we can reference it,
     * but we might just need to fetch it dynamically or just use the ID.
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }
}
