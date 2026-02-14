<?php

namespace App\Modules\Registration\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Registration\Services\ClassroomConflictDetector;
use App\Modules\Registration\Models\Classroom;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;

class ClassroomConflictDetectorTest extends TestCase
{
    use RefreshDatabase;

    protected ClassroomConflictDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ClassroomConflictDetector();
    }

    public function test_detects_conflict_when_classroom_is_double_booked(): void
    {
        $classroom = Classroom::factory()->create();
        $section = Section::factory()->create(['classroom_id' => $classroom->classroom_id]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $conflict = $this->detector->detect(
            $classroom->classroom_id,
            'Monday',
            '09:30',
            '11:00'
        );

        $this->assertNotNull($conflict);
    }

    public function test_no_conflict_when_times_do_not_overlap(): void
    {
        $classroom = Classroom::factory()->create();
        $section = Section::factory()->create(['classroom_id' => $classroom->classroom_id]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $conflict = $this->detector->detect(
            $classroom->classroom_id,
            'Monday',
            '10:00',
            '11:00'
        );

        $this->assertNull($conflict);
    }

    public function test_no_conflict_on_different_days(): void
    {
        $classroom = Classroom::factory()->create();
        $section = Section::factory()->create(['classroom_id' => $classroom->classroom_id]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $conflict = $this->detector->detect(
            $classroom->classroom_id,
            'Tuesday',
            '09:00',
            '10:30'
        );

        $this->assertNull($conflict);
    }

    public function test_no_conflict_for_empty_classroom(): void
    {
        $classroom = Classroom::factory()->create();

        $conflict = $this->detector->detect(
            $classroom->classroom_id,
            'Monday',
            '09:00',
            '10:00'
        );

        $this->assertNull($conflict);
    }

    public function test_excludes_specified_schedule_from_conflict_check(): void
    {
        $classroom = Classroom::factory()->create();
        $section = Section::factory()->create(['classroom_id' => $classroom->classroom_id]);

        $schedule = Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        // Exclude the existing schedule (for update scenarios)
        $conflict = $this->detector->detect(
            $classroom->classroom_id,
            'Monday',
            '09:00',
            '10:30',
            $schedule->schedule_id
        );

        $this->assertNull($conflict);
    }

    public function test_is_available_returns_true_for_open_slot(): void
    {
        $classroom = Classroom::factory()->create();

        $this->assertTrue($this->detector->isAvailable(
            $classroom->classroom_id,
            'Wednesday',
            '14:00',
            '15:30'
        ));
    }

    public function test_is_available_returns_false_for_booked_slot(): void
    {
        $classroom = Classroom::factory()->create();
        $section = Section::factory()->create(['classroom_id' => $classroom->classroom_id]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Wednesday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);

        $this->assertFalse($this->detector->isAvailable(
            $classroom->classroom_id,
            'Wednesday',
            '14:30',
            '16:00'
        ));
    }
}
