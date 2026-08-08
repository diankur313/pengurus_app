<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotKnowledgeBase extends Model
{
    protected $fillable = [
        'topik',
        'deskripsi',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];
}
