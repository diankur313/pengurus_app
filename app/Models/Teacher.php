<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'photo',
        'name',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'education_history',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }
}
