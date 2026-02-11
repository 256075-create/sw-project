<?php

namespace App\Modules\Academic\Database\Factories;

use App\Modules\Academic\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

class UniversityFactory extends Factory
{
    protected $model = University::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' University',
            'code' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
