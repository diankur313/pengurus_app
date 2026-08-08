<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherAttendanceFactory extends Factory
{
    protected $model = TeacherAttendance::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'attendance_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
