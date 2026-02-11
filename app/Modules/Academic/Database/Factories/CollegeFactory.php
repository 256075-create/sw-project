<?php

namespace App\Modules\Academic\Database\Factories;

use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollegeFactory extends Factory
{
    protected $model = College::class;

    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'name' => 'College of ' . fake()->randomElement(['Engineering', 'Science', 'Arts', 'Business', 'Medicine', 'Law', 'Education']),
            'code' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
