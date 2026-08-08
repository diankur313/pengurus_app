<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'name'             => fake()->name(),
            'photo'            => null,
            'tempat_lahir'     => fake()->city(),
            'tanggal_lahir'    => fake()->date(),
            'gender'           => fake()->randomElement(['pria', 'wanita']),
            'education_history' => fake()->sentence(),
        ];
    }
}
