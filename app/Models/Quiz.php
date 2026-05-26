<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'description',
        'duration',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EducationSchedule::class);
    }

    /**
     * Check if quiz contains any essay questions.
     */
    public function hasEssayQuestions(): bool
    {
        return $this->questions()->where('type', 'essay')->exists();
    }
}
