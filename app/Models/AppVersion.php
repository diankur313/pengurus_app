<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'platform', 'version_name', 'version_code',
        'is_mandatory', 'custom_message', 'download_url',
        'changelog', 'released_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'released_at' => 'datetime',
    ];
}
