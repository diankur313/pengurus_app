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

    /**
     * Get the name of the user who used this voucher.
     */
    public function getUsedByNameAttribute(): ?string
    {
        if (!$this->used_by_user_id) {
            return null;
        }

        // 1. Try legacy member table (yisic_db_lama.member)
        try {
            $member = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')
                ->table('member')
                ->where('user_id', $this->used_by_user_id)
                ->first();
            if ($member && !empty($member->member_name)) {
                return $member->member_name;
            }
        } catch (\Exception $e) {
            // ignore
        }

        // 2. Try legacy sys_users table (yisic_db_lama.sys_users)
        try {
            $sysUser = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')
                ->table('sys_users')
                ->where('id', $this->used_by_user_id)
                ->first();
            if ($sysUser && !empty($sysUser->first_name)) {
                return trim($sysUser->first_name . ' ' . ($sysUser->last_name ?? ''));
            }
        } catch (\Exception $e) {
            // ignore
        }

        // 3. Try new member table (ppab.ppab_member) via id_member
        try {
            $ppabMember = \Illuminate\Support\Facades\DB::connection('ppab')
                ->table('ppab_member')
                ->where('id_member', $this->used_by_user_id)
                ->first();
            if ($ppabMember && !empty($ppabMember->name)) {
                return $ppabMember->name;
            }
        } catch (\Exception $e) {
            // ignore
        }

        // 4. Try new member table (ppab.ppab_member) via id
        try {
            $ppabMemberById = \Illuminate\Support\Facades\DB::connection('ppab')
                ->table('ppab_member')
                ->where('id', $this->used_by_user_id)
                ->first();
            if ($ppabMemberById && !empty($ppabMemberById->name)) {
                return $ppabMemberById->name;
            }
        } catch (\Exception $e) {
            // ignore
        }

        // 5. Try admin users table (ppab.users)
        try {
            $ppabUser = \Illuminate\Support\Facades\DB::connection('ppab')
                ->table('users')
                ->where('id', $this->used_by_user_id)
                ->first();
            if ($ppabUser && !empty($ppabUser->name)) {
                return $ppabUser->name;
            }
        } catch (\Exception $e) {
            // ignore
        }

        return 'User ID: ' . $this->used_by_user_id;
    }
}
