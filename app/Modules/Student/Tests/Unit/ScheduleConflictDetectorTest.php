<?php

namespace App\Modules\Student\Tests\Unit;

use Tests\TestCase;
use App\Modules\Student\Services\ScheduleConflictDetector;
use App\Modules\Registration\Models\Schedule;

class ScheduleConflictDetectorTest extends TestCase
{
    protected ScheduleConflictDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ScheduleConflictDetector();
    }

    public function test_detects_overlapping_schedules_on_same_day(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);

        $this->assertTrue($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_no_conflict_on_different_days(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Tuesday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $this->assertFalse($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_no_conflict_when_schedules_are_adjacent(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertFalse($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_detects_complete_overlap(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Wednesday',
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Wednesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertTrue($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_detects_exact_same_time(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Thursday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Thursday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);

        $this->assertTrue($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_no_conflict_when_non_overlapping_same_day(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Friday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Friday',
            'start_time' => '11:00',
            'end_time' => '12:00',
        ]);

        $this->assertFalse($this->detector->hasConflict($scheduleA, $scheduleB));
    }

    public function test_detects_partial_overlap_at_start(): void
    {
        $scheduleA = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '08:30',
            'end_time' => '09:30',
        ]);

        $scheduleB = new Schedule([
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $this->assertTrue($this->detector->hasConflict($scheduleA, $scheduleB));
    }
}
