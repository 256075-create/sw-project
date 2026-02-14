<?php

namespace App\Modules\Student\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Student\Services\TimetableService;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\Enrollment;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;

class TimetableServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TimetableService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TimetableService::class);
    }

    public function test_returns_empty_timetable_for_student_with_no_enrollments(): void
    {
        $student = Student::factory()->create();

        $timetable = $this->service->getWeeklyTimetable($student->student_id);

        $this->assertIsArray($timetable);
        $this->assertCount(7, $timetable);

        foreach ($timetable as $day => $slots) {
            $this->assertEmpty($slots, "Expected empty slots for {$day}");
        }
    }

    public function test_returns_timetable_with_enrolled_courses(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $timetable = $this->service->getWeeklyTimetable($student->student_id);

        $this->assertNotEmpty($timetable['Monday']);
        $this->assertStringStartsWith('09:00', $timetable['Monday'][0]['start_time'] ?? '');
        $this->assertArrayHasKey('course_name', $timetable['Monday'][0]);
        $this->assertArrayHasKey('instructor_name', $timetable['Monday'][0]);
        $this->assertArrayHasKey('classroom', $timetable['Monday'][0]);
    }

    public function test_excludes_dropped_enrollments(): void
    {
        $student = Student::factory()->create();

        $activeSection = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);
        Schedule::factory()->create([
            'section_id' => $activeSection->section_id,
            'day_of_week' => 'Tuesday',
            'start_time' => '11:00',
            'end_time' => '12:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $activeSection->section_id,
            'status' => 'enrolled',
        ]);

        $droppedSection = Section::factory()->create(['max_capacity' => 30]);
        Schedule::factory()->create([
            'section_id' => $droppedSection->section_id,
            'day_of_week' => 'Wednesday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $droppedSection->section_id,
            'status' => 'dropped',
        ]);

        $timetable = $this->service->getWeeklyTimetable($student->student_id);

        $this->assertNotEmpty($timetable['Tuesday']);
        $this->assertEmpty($timetable['Wednesday']);
    }

    public function test_sorts_slots_by_start_time(): void
    {
        $student = Student::factory()->create();

        // Create two sections on the same day, add later one first
        $section1 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);
        Schedule::factory()->create([
            'section_id' => $section1->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section1->section_id,
            'status' => 'enrolled',
        ]);

        $section2 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);
        Schedule::factory()->create([
            'section_id' => $section2->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section2->section_id,
            'status' => 'enrolled',
        ]);

        $timetable = $this->service->getWeeklyTimetable($student->student_id);

        $this->assertCount(2, $timetable['Monday']);
        $this->assertStringStartsWith('09:00', $timetable['Monday'][0]['start_time'] ?? '');
        $this->assertStringStartsWith('14:00', $timetable['Monday'][1]['start_time'] ?? '');
    }

    public function test_get_day_timetable_returns_only_specific_day(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Friday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $fridayTimetable = $this->service->getDayTimetable($student->student_id, 'Friday');
        $mondayTimetable = $this->service->getDayTimetable($student->student_id, 'Monday');

        $this->assertNotEmpty($fridayTimetable);
        $this->assertEmpty($mondayTimetable);
    }

    public function test_export_timetable_includes_summary(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);

        Schedule::factory()->create([
            'section_id' => $section->section_id,
            'day_of_week' => 'Thursday',
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $export = $this->service->exportTimetable($student->student_id);

        $this->assertEquals($student->student_id, $export['student_id']);
        $this->assertEquals(1, $export['total_courses']);
        $this->assertArrayHasKey('total_credits', $export);
        $this->assertArrayHasKey('courses', $export);
        $this->assertArrayHasKey('timetable', $export);
    }
}
