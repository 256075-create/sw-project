<?php

namespace App\Modules\Academic\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Academic\Services\AcademicStructureService;
use App\Modules\Academic\Models\University;
use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;

class AcademicStructureServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicStructureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AcademicStructureService::class);
    }

    public function test_can_create_university(): void
    {
        $university = $this->service->createUniversity([
            'name' => 'Test University',
            'code' => 'TU',
        ]);

        $this->assertInstanceOf(University::class, $university);
        $this->assertEquals('Test University', $university->name);
        $this->assertEquals('TU', $university->code);
        $this->assertDatabaseHas('academic_universities', ['code' => 'TU']);
    }

    public function test_can_update_university(): void
    {
        $university = University::factory()->create();

        $updated = $this->service->updateUniversity($university->university_id, [
            'name' => 'Updated University',
        ]);

        $this->assertEquals('Updated University', $updated->name);
    }

    public function test_can_delete_university(): void
    {
        $university = University::factory()->create();

        $result = $this->service->deleteUniversity($university->university_id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('academic_universities', ['university_id' => $university->university_id]);
    }

    public function test_can_create_college_under_university(): void
    {
        $university = University::factory()->create();

        $college = $this->service->createCollege([
            'university_id' => $university->university_id,
            'name' => 'College of Engineering',
            'code' => 'ENG',
        ]);

        $this->assertInstanceOf(College::class, $college);
        $this->assertEquals($university->university_id, $college->university_id);
    }

    public function test_can_create_department_under_college(): void
    {
        $college = College::factory()->create();

        $department = $this->service->createDepartment([
            'college_id' => $college->college_id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals($college->college_id, $department->college_id);
    }

    public function test_can_get_hierarchy(): void
    {
        $university = University::factory()->create();
        $college = College::factory()->create(['university_id' => $university->university_id]);
        $department = Department::factory()->create(['college_id' => $college->college_id]);
        Major::factory()->create(['department_id' => $department->department_id]);

        $hierarchy = $this->service->getHierarchy();

        $this->assertNotEmpty($hierarchy);
        $first = $hierarchy->first();
        $this->assertTrue($first->relationLoaded('colleges'));
    }

    public function test_can_list_universities_with_pagination(): void
    {
        University::factory()->count(20)->create();

        $result = $this->service->listUniversities([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }
}
