<?php

namespace App\Modules\Registration\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Registration\Services\CourseService;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Section;

class CourseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CourseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CourseService();
    }

    public function test_can_create_course(): void
    {
        $course = $this->service->create([
            'course_code' => 'CS101',
            'name' => 'Intro to CS',
            'credit_hours' => 3,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('CS101', $course->course_code);
        $this->assertDatabaseHas('registration_courses', ['course_code' => 'CS101']);
    }

    public function test_can_update_course(): void
    {
        $course = Course::factory()->create();

        $updated = $this->service->update($course->course_id, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_can_delete_course_without_sections(): void
    {
        $course = Course::factory()->create();

        $result = $this->service->delete($course->course_id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('registration_courses', ['course_id' => $course->course_id]);
    }

    public function test_cannot_delete_course_with_sections(): void
    {
        $course = Course::factory()->create();
        Section::factory()->create(['course_id' => $course->course_id]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->delete($course->course_id);
    }

    public function test_can_activate_course(): void
    {
        $course = Course::factory()->create(['is_active' => false]);

        $activated = $this->service->activate($course->course_id);

        $this->assertTrue($activated->is_active);
    }

    public function test_can_deactivate_course(): void
    {
        $course = Course::factory()->create(['is_active' => true]);

        $deactivated = $this->service->deactivate($course->course_id);

        $this->assertFalse($deactivated->is_active);
    }

    public function test_can_find_course_by_code(): void
    {
        $course = Course::factory()->create(['course_code' => 'CS999']);

        $found = $this->service->findByCode('CS999');

        $this->assertNotNull($found);
        $this->assertEquals($course->course_id, $found->course_id);
    }

    public function test_can_list_courses_with_pagination(): void
    {
        Course::factory()->count(20)->create();

        $result = $this->service->list([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }
}
