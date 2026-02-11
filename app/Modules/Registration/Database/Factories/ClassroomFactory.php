<?php

namespace App\Modules\Registration\Database\Factories;

use App\Modules\Registration\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'room_number' => fake()->unique()->numerify('R-###'),
            'building' => fake()->randomElement(['Building A', 'Building B', 'Building C', 'Engineering Hall', 'Science Center']),
            'capacity' => fake()->numberBetween(20, 100),
        ];
    }
}
