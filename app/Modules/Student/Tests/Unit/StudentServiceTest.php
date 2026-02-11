<?php

namespace App\Modules\Student\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Student\Services\StudentService;
use App\Modules\Student\Models\Student;
use App\Modules\Academic\Models\Major;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StudentService::class);
    }

    public function test_can_create_student(): void
    {
        $major = Major::factory()->create();

        $student = $this->service->create([
            'major_id' => $major->major_id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
        ]);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('John', $student->first_name);
        $this->assertNotNull($student->student_number);
        $this->assertEquals('active', $student->status);
    }

    public function test_generates_unique_student_number(): void
    {
        $major = Major::factory()->create();

        $s1 = $this->service->create(['major_id' => $major->major_id, 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@test.com']);
        $s2 = $this->service->create(['major_id' => $major->major_id, 'first_name' => 'C', 'last_name' => 'D', 'email' => 'b@test.com']);

        $this->assertNotEquals($s1->student_number, $s2->student_number);
    }

    public function test_can_update_student(): void
    {
        $student = Student::factory()->create();

        $updated = $this->service->update($student->student_id, ['first_name' => 'Updated']);

        $this->assertEquals('Updated', $updated->first_name);
    }

    public function test_can_find_student_by_student_number(): void
    {
        $student = Student::factory()->create(['student_number' => 'STU-2024-9999']);

        $found = $this->service->findByStudentNumber('STU-2024-9999');

        $this->assertNotNull($found);
        $this->assertEquals($student->student_id, $found->student_id);
    }

    public function test_can_delete_student(): void
    {
        $student = Student::factory()->create();

        $result = $this->service->delete($student->student_id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('student_students', ['student_id' => $student->student_id]);
    }

    public function test_can_list_students_with_filters(): void
    {
        Student::factory()->count(5)->create(['status' => 'active']);
        Student::factory()->count(3)->create(['status' => 'inactive']);

        $result = $this->service->list(['status' => 'active'], 10);

        $this->assertEquals(5, $result->total());
    }
}
