<?php

namespace App\Modules\Student\Database\Factories;

use App\Modules\Student\Models\Enrollment;
use App\Modules\Student\Models\Student;
use App\Modules\Registration\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'section_id' => Section::factory(),
            'enrollment_date' => now(),
            'status' => 'enrolled',
        ];
    }

    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dropped',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
