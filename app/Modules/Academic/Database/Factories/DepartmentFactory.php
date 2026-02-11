<?php

namespace App\Modules\Academic\Database\Factories;

use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'college_id' => College::factory(),
            'name' => fake()->randomElement(['Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'English']) . ' Department',
            'code' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
