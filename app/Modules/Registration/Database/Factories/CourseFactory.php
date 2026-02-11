<?php

namespace App\Modules\Registration\Database\Factories;

use App\Modules\Registration\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'course_code' => strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'credit_hours' => fake()->numberBetween(1, 4),
            'is_active' => true,
        ];
    }
}
