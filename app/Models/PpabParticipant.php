<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabParticipant extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_member';
    protected $guarded = [];

    /**
     * Relasi ke satu record absensi peserta (tanpa filter session).
     */
    public function attendance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PpabAttendance::class, 'member_id', 'uuid');
    }

    /**
     * Relasi ke transaksi pembayaran PAID peserta.
     */
    public function paidPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PpabPayment::class, 'id_member', 'uuid')
            ->where('status', 'PAID');
    }
}
