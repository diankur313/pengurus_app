<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabRegistration extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_sessions';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = substr((string) \Illuminate\Support\Str::uuid(), 0, 32);
            }
        });
    }
}
