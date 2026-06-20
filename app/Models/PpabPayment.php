<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentGatewayFee;

class PpabPayment extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_transactions_xendit';
    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (!empty($model->id_apps) && empty($model->fee_sysdev)) {
                $feeRule = PaymentGatewayFee::where('app_id', $model->id_apps)->first();
                if ($feeRule) {
                    $model->fee_sysdev = $feeRule->sysdev_fee;
                }
            }
        });
    }

    public function member()
    {
        return $this->belongsTo(PpabParticipant::class, 'id_member', 'uuid');
    }
}
