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
        'quiz_id',
        'type',
        'title',
        'level',
        'start_at',
        'end_at',
        // Kehadiran
        'attendance_mode',
        // Google Meet
        'meeting_link',
        'google_event_id',
        'google_space_name',
        'meet_access_type',
        'meet_entry_point_access',
        'meet_moderation',
        'meet_description',
        'meet_co_host_email',
        // Reminder
        'send_reminder',
        'reminder_before',
        'reminder_sent',
    ];


    protected $casts = [
        'start_at'      => 'datetime',
        'end_at'        => 'datetime',
        'send_reminder' => 'boolean',
        'reminder_sent' => 'boolean',

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

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
