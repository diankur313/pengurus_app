<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'civitas_id',
        'schedule_id',
        'scanned_by_user_id',
        'status',
    ];

    public function civitas()
    {
        return $this->belongsTo(CivitasPendidikan::class, 'civitas_id', 'uuid');
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(EducationSchedule::class, 'schedule_id', 'uuid');
    }
}
