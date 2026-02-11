<?php

namespace App\Modules\Student\Database\Factories;

use App\Modules\Student\Models\Student;
use App\Modules\Academic\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'major_id' => Major::factory(),
            'student_number' => 'STU-' . date('Y') . '-' . fake()->unique()->numerify('####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'enrollment_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function graduated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graduated',
        ]);
    }
}
