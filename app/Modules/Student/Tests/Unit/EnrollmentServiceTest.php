<?php

namespace App\Modules\Student\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Student\Services\EnrollmentService;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\Enrollment;
use App\Modules\Student\Exceptions\SectionFullException;
use App\Modules\Student\Exceptions\DuplicateEnrollmentException;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Classroom;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;

class EnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EnrollmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EnrollmentService::class);
    }

    public function test_can_enroll_student_in_section(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);

        $enrollment = $this->service->enroll($student->student_id, $section->section_id);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals('enrolled', $enrollment->status);
        $this->assertEquals($student->student_id, $enrollment->student_id);
        $this->assertEquals($section->section_id, $enrollment->section_id);
    }

    public function test_enrollment_increments_section_count(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 5]);

        $this->service->enroll($student->student_id, $section->section_id);

        $section->refresh();
        $this->assertEquals(6, $section->current_enrollment);
    }

    public function test_cannot_enroll_in_full_section(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 1, 'current_enrollment' => 1]);

        $this->expectException(SectionFullException::class);
        $this->service->enroll($student->student_id, $section->section_id);
    }

    public function test_cannot_duplicate_enrollment(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);

        $this->service->enroll($student->student_id, $section->section_id);

        $this->expectException(DuplicateEnrollmentException::class);
        $this->service->enroll($student->student_id, $section->section_id);
    }

    public function test_can_drop_enrollment(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $dropped = $this->service->drop($enrollment->enrollment_id);

        $this->assertEquals('dropped', $dropped->status);
    }

    public function test_drop_decrements_section_count(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 5]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $this->service->drop($enrollment->enrollment_id);

        $section->refresh();
        $this->assertEquals(4, $section->current_enrollment);
    }

    public function test_can_get_student_timetable(): void
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

        $timetable = $this->service->getStudentTimetable($student->student_id);

        $this->assertIsArray($timetable);
        $this->assertArrayHasKey('Monday', $timetable);
        $this->assertNotEmpty($timetable['Monday']);
    }
}
