<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PpabRegistration extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_sessions';
    protected $guarded = [];

    /**
     * Peta prefix paket ke ticket_type string yang ada di ppab_ticket_locks.
     * Dipakai untuk menghitung berapa slot yang sedang di-hold user saat admin edit quota.
     */
    private static array $pkgKeyMap = [
        'sii_'     => 'sii',
        'bsq_'     => 'bsq',
        'sii_bsq_' => 'sii_bsq',
    ];

    /**
     * Peta nama quota column (tanpa prefix) ke lock ticket_type suffix yang mengonsumsinya.
     * Khusus quota_full: b2 dan b3 juga ambil dari kolom ini (qty-nya 2 dan 3).
     */
    private static array $quotaTypeMap = [
        'quota_full'       => ['paket-full', 'paket-b2', 'paket-b3'],
        'quota_dp'         => ['paket-dp'],
        'quota_early_bird' => ['paket-eb'],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = substr((string) \Illuminate\Support\Str::uuid(), 0, 32);
            }
            // Sesi baru: tidak ada locks, live = original
            self::applyOriginalToLive($model, sessionId: null);
        });

        static::updating(function ($model) {
            // Sesi existing: live = original - active lock qty
            self::applyOriginalToLive($model, sessionId: $model->id);
        });
    }

    /**
     * Sync kolom live (*_quota_full dll.) dari kolom _original.
     * Jika sessionId tersedia, kurangi dengan qty lock yang sedang aktif
     * agar user yang sedang di halaman pilih tiket tidak kehilangan reservasinya.
     */
    private static function applyOriginalToLive(self $model, ?int $sessionId): void
    {
        foreach (self::$pkgKeyMap as $prefix => $pkgKey) {
            foreach (self::$quotaTypeMap as $quotaCol => $lockSuffixes) {
                $originalCol = $prefix . $quotaCol . '_original';
                $liveCol     = $prefix . $quotaCol;

                // Hanya proses jika kolom original ada dan berubah (atau baru)
                if (!array_key_exists($originalCol, $model->getAttributes())) {
                    continue;
                }

                $newOriginal = (int) ($model->getAttribute($originalCol) ?? 0);

                // Hitung total qty lock aktif untuk ticket types ini di session
                $activeLockQty = 0;
                if ($sessionId !== null) {
                    $lockTypes = array_map(fn($s) => $pkgKey . '-' . $s, $lockSuffixes);
                    $activeLockQty = (int) DB::connection('ppab')
                        ->table('ppab_ticket_locks')
                        ->where('session_id', $sessionId)
                        ->whereIn('ticket_type', $lockTypes)
                        ->sum('qty');
                }

                $model->setAttribute($liveCol, max(0, $newOriginal - $activeLockQty));
            }
        }
    }
}

