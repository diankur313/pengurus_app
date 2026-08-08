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
        'paket',
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
        'paket'         => 'array',

    ];

    // ─── Level / Angkatan ──────────────────────────────────────────────────
    public const LEVEL_GENERAL    = 'general';
    public const LEVEL_SEMESTER_1 = 'semester_1';
    public const LEVEL_SEMESTER_2 = 'semester_2';
    public const LEVEL_SEMESTER_3 = 'semester_3';

    /**
     * Opsi Angkatan untuk form (add & edit jadwal).
     * General = berlaku untuk Semester 1, Semester 2, dan Semester 3.
     */
    public static function levelOptions(): array
    {
        return [
            self::LEVEL_GENERAL    => 'General (Semester 1, Semester 2 & Semester 3)',
            self::LEVEL_SEMESTER_1 => 'Semester 1',
            self::LEVEL_SEMESTER_2 => 'Semester 2',
            self::LEVEL_SEMESTER_3 => 'Semester 3',
        ];
    }

    /**
     * Label tampilan untuk nilai level saat ini.
     */
    public function levelLabel(): string
    {
        return self::levelOptions()[$this->level] ?? ucfirst((string) $this->level);
    }

    /**
     * Daftar level_angkatan (CivitasPendidikan) yang menjadi target jadwal ini.
     * General → semester_1 + semester_2 + semester_3.
     */
    public function levelTargets(): array
    {
        if ($this->level === self::LEVEL_GENERAL) {
            return [self::LEVEL_SEMESTER_1, self::LEVEL_SEMESTER_2, self::LEVEL_SEMESTER_3];
        }

        return [$this->level];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::random(12);
            }

            if ($model->type === 'bsq' && empty($model->title)) {
                $shortLevel = match ($model->level) {
                    self::LEVEL_SEMESTER_1 => 'Semester 1',
                    self::LEVEL_SEMESTER_2 => 'Semester 2',
                    self::LEVEL_SEMESTER_3 => 'Semester 3',
                    self::LEVEL_GENERAL    => 'General',
                    default                => ucfirst((string) $model->level),
                };
                $model->title = "BSQ - {$shortLevel}";
            }
        });

        // Normalisasi paket: hanya relevan untuk type=quiz, dan "sii + bsq" adalah
        // shortcut untuk pilih keduanya sekaligus (bukan kategori tersendiri) supaya
        // matching di e-sii cukup mengecek token atomic 'sii'/'bsq'.
        static::saving(function ($model) {
            if ($model->type !== 'quiz') {
                $model->paket = null;
                return;
            }

            $paket = $model->paket;
            if (empty($paket)) {
                $model->paket = null;
                return;
            }

            $expanded = [];
            foreach ($paket as $p) {
                if ($p === 'sii + bsq') {
                    $expanded[] = 'sii';
                    $expanded[] = 'bsq';
                } else {
                    $expanded[] = $p;
                }
            }
            $model->paket = array_values(array_unique($expanded));
        });

        // Hapus Google Calendar event & Meet space saat model didelete
        static::deleting(function ($model) {
            if ($model->google_event_id) {
                try {
                    $service = new \App\Services\GoogleMeetService();
                    $service->deleteMeeting($model->google_event_id, $model->google_space_name);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal menghapus Google Meet saat model deleting', [
                        'schedule_id' => $model->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Hapus Google Calendar event & Meet space jika diubah dari online ke offline
        static::updating(function ($model) {
            if ($model->isDirty('attendance_mode') && $model->attendance_mode === 'offline') {
                if ($model->google_event_id) {
                    try {
                        $service = new \App\Services\GoogleMeetService();
                        $service->deleteMeeting($model->google_event_id, $model->google_space_name);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Gagal menghapus Google Meet saat model switching to offline', [
                            'schedule_id' => $model->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    $model->meeting_link = null;
                    $model->google_event_id = null;
                    $model->google_space_name = null;
                    $model->reminder_sent = false;
                }
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
