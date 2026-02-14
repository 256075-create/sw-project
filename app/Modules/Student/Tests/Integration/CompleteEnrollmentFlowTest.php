<?php

namespace App\Modules\Student\Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use App\Modules\Student\Models\Student;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompleteEnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected string $accessToken;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'integrationuser',
            'email' => 'integration@test.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Admin', 'description' => 'Admin']);

        $permissions = [
            ['permission_name' => 'enrollments.read', 'resource' => 'enrollments', 'action' => 'read'],
            ['permission_name' => 'enrollments.enroll', 'resource' => 'enrollments', 'action' => 'enroll'],
            ['permission_name' => 'enrollments.drop', 'resource' => 'enrollments', 'action' => 'drop'],
            ['permission_name' => 'timetable.view', 'resource' => 'timetable', 'action' => 'view'],
            ['permission_name' => 'students.read', 'resource' => 'students', 'action' => 'read'],
        ];

        foreach ($permissions as $permData) {
            $perm = Permission::create($permData);
            $role->permissions()->attach($perm->permission_id);
        }

        $this->user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'integrationuser',
            'password' => 'password123',
        ]);

        $this->accessToken = $response->json('access_token');
    }

    /** @test */
    public function complete_enrollment_flow_enroll_view_timetable_drop(): void
    {
        $student = Student::factory()->create();

        // Create two sections with schedules on different days
        $section1 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section1->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $section2 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section2->section_id,
            'day_of_week' => 'Wednesday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);

        $headers = ['Authorization' => "Bearer {$this->accessToken}"];

        // Step 1: Enroll in first section
        $enroll1 = $this->withHeaders($headers)
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section1->section_id,
            ]);
        $enroll1->assertStatus(201);
        $enrollment1Id = $enroll1->json('enrollment_id');

        // Step 2: Enroll in second section
        $enroll2 = $this->withHeaders($headers)
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section2->section_id,
            ]);
        $enroll2->assertStatus(201);
        $enrollment2Id = $enroll2->json('enrollment_id');

        // Step 3: View timetable - should have entries on Monday and Wednesday
        $timetable = $this->withHeaders($headers)
            ->getJson("/api/students/{$student->student_id}/timetable");
        $timetable->assertStatus(200);

        $data = $timetable->json('data');
        $this->assertNotEmpty($data['Monday']);
        $this->assertNotEmpty($data['Wednesday']);
        $this->assertEmpty($data['Tuesday']);

        // Step 4: List enrollments
        $enrollments = $this->withHeaders($headers)
            ->getJson("/api/students/{$student->student_id}/enrollments");
        $enrollments->assertStatus(200);

        // Step 5: Drop second enrollment
        $drop = $this->withHeaders($headers)
            ->postJson("/api/enrollments/{$enrollment2Id}/drop");
        $drop->assertStatus(200);
        $this->assertEquals('dropped', $drop->json('status'));

        // Step 6: Verify timetable updated - only Monday now
        $timetable2 = $this->withHeaders($headers)
            ->getJson("/api/students/{$student->student_id}/timetable");
        $timetable2->assertStatus(200);

        $data2 = $timetable2->json('data');
        $this->assertNotEmpty($data2['Monday']);
        $this->assertEmpty($data2['Wednesday']);

        // Step 7: Verify section enrollment count decremented
        $section2->refresh();
        $this->assertEquals(0, $section2->current_enrollment);
    }

    /** @test */
    public function cannot_enroll_in_conflicting_schedule_then_enroll_after_drop(): void
    {
        $student = Student::factory()->create();
        $headers = ['Authorization' => "Bearer {$this->accessToken}"];

        // Section 1: Monday 09:00-10:30
        $section1 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section1->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        // Section 2: Monday 10:00-11:30 (overlaps with section 1)
        $section2 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section2->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);

        // Enroll in section 1
        $enroll1 = $this->withHeaders($headers)
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section1->section_id,
            ]);
        $enroll1->assertStatus(201);
        $enrollment1Id = $enroll1->json('enrollment_id');

        // Try to enroll in section 2 - should conflict
        $enroll2 = $this->withHeaders($headers)
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section2->section_id,
            ]);
        $enroll2->assertStatus(409);

        // Drop section 1
        $this->withHeaders($headers)
            ->postJson("/api/enrollments/{$enrollment1Id}/drop")
            ->assertStatus(200);

        // Now enroll in section 2 - should succeed
        $enroll3 = $this->withHeaders($headers)
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section2->section_id,
            ]);
        $enroll3->assertStatus(201);
    }
}
