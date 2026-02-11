<?php

namespace App\Modules\Academic\Database\Factories;

use App\Modules\Academic\Models\Major;
use App\Modules\Academic\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class MajorFactory extends Factory
{
    protected $model = Major::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => fake()->randomElement(['Software Engineering', 'Data Science', 'Cybersecurity', 'Artificial Intelligence', 'Networks', 'Information Systems']),
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'total_credits' => fake()->numberBetween(120, 160),
        ];
    }
}
