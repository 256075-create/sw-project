<?php

namespace App\Modules\Registration\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Registration\Services\SectionService;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Classroom;
use App\Modules\Registration\Models\Section;

class SectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SectionService();
    }

    public function test_can_create_section(): void
    {
        $course = Course::factory()->create();
        $classroom = Classroom::factory()->create();

        $section = $this->service->create([
            'course_id' => $course->course_id,
            'classroom_id' => $classroom->classroom_id,
            'section_number' => 'SEC-001',
            'instructor_name' => 'Dr. Smith',
            'max_capacity' => 30,
            'current_enrollment' => 0,
            'semester' => 'Fall',
            'academic_year' => '2024-2025',
        ]);

        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals('SEC-001', $section->section_number);
    }

    public function test_can_increment_enrollment(): void
    {
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);

        $updated = $this->service->incrementEnrollment($section->section_id);

        $this->assertEquals(1, $updated->current_enrollment);
    }

    public function test_cannot_increment_past_capacity(): void
    {
        $section = Section::factory()->create(['max_capacity' => 1, 'current_enrollment' => 1]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->incrementEnrollment($section->section_id);
    }

    public function test_can_decrement_enrollment(): void
    {
        $section = Section::factory()->create(['current_enrollment' => 5]);

        $updated = $this->service->decrementEnrollment($section->section_id);

        $this->assertEquals(4, $updated->current_enrollment);
    }

    public function test_cannot_decrement_below_zero(): void
    {
        $section = Section::factory()->create(['current_enrollment' => 0]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->decrementEnrollment($section->section_id);
    }

    public function test_cannot_delete_section_with_enrollments(): void
    {
        $section = Section::factory()->create(['current_enrollment' => 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->delete($section->section_id);
    }

    public function test_cannot_reduce_capacity_below_enrollment(): void
    {
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 20]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->update($section->section_id, ['max_capacity' => 10]);
    }
}
