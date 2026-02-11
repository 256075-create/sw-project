<?php

namespace App\Modules\Registration\Database\Factories;

use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'classroom_id' => Classroom::factory(),
            'section_number' => 'SEC-' . fake()->unique()->numberBetween(1, 999),
            'instructor_name' => fake()->name(),
            'max_capacity' => fake()->numberBetween(20, 60),
            'current_enrollment' => 0,
            'semester' => fake()->randomElement(['Fall', 'Spring', 'Summer']),
            'academic_year' => '2024-2025',
        ];
    }
}
