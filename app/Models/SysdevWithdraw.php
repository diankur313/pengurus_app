<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel sysdev_withdraws di database PPAB.
 * Digunakan oleh XenditWebhookController untuk update status disbursement DISB/SYS.
 */
class SysdevWithdraw extends Model
{
    protected $connection = 'ppab';
    protected $table = 'sysdev_withdraws';
    protected $guarded = ['id'];
}
