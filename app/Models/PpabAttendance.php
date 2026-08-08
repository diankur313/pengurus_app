<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabAttendance extends Model
{
    protected $connection = 'ppab';
    protected $table      = 'ppab_attendance';

    /**
     * Kolom yang dapat diisi massal.
     */
    protected $fillable = [
        'member_id',
        'session_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Nonaktifkan auto-timestamp bawaan Laravel karena:
     * - created_at bertipe VARCHAR (bukan DATETIME/TIMESTAMP)
     * - Kita akan set created_at secara manual saat insert
     */
    public $timestamps = false;

    /**
     * Relasi ke peserta PPAB berdasarkan UUID.
     */
    public function participant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PpabParticipant::class, 'member_id', 'uuid');
    }

    /**
     * Relasi ke sesi PPAB berdasarkan UUID sesi.
     */
    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PpabRegistration::class, 'session_id', 'uuid');
    }

    /**
     * Ambil data pembayaran peserta (satu record PAID untuk sesi yang sama).
     */
    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PpabPayment::class, 'id_member', 'member_id');
    }
}
