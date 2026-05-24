<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSubmission extends Model
{
    protected $fillable = [
        'quiz_id',
        'civitas_id',
        'education_schedule_id',
        'total_score',
        'mc_score',
        'essay_score',
        'status',
        'started_at',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'total_score' => 'decimal:2',
        'mc_score' => 'decimal:2',
        'essay_score' => 'decimal:2',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function civitas(): BelongsTo
    {
        return $this->belongsTo(CivitasPendidikan::class, 'civitas_id', 'uuid');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EducationSchedule::class, 'education_schedule_id');
    }
}
