<?php

namespace App\Modules\Registration\Database\Factories;

use App\Modules\Registration\Models\Schedule;
use App\Modules\Registration\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $startTime = sprintf('%02d:00', $startHour);
        $endTime = sprintf('%02d:00', $startHour + 1);

        return [
            'section_id' => Section::factory(),
            'day_of_week' => fake()->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
