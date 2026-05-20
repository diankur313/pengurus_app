<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationMaterial extends Model
{
    protected $fillable = [
        'education_schedule_id',
        'file_path',
    ];

    protected $casts = [
        'file_path' => 'array',
    ];

    public function educationSchedule(): BelongsTo
    {
        return $this->belongsTo(EducationSchedule::class);
    }
}
