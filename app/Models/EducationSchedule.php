<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EducationSchedule extends Model
{
    protected $fillable = [
        'uuid',
        'teacher_id',
        'type',
        'title',
        'level',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::random(12);
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function educationMaterials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EducationMaterial::class);
    }
}
