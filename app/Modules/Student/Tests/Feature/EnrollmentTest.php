<?php

namespace App\Modules\Student\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\Enrollment;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Classroom;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected string $accessToken;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'staffuser',
            'email' => 'staff@test.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Registration Staff', 'description' => 'Staff role']);

        $permissions = [
            ['permission_name' => 'enrollments.read', 'resource' => 'enrollments', 'action' => 'read'],
            ['permission_name' => 'enrollments.enroll', 'resource' => 'enrollments', 'action' => 'enroll'],
            ['permission_name' => 'enrollments.drop', 'resource' => 'enrollments', 'action' => 'drop'],
            ['permission_name' => 'timetable.view', 'resource' => 'timetable', 'action' => 'view'],
        ];

        foreach ($permissions as $permData) {
            $perm = Permission::create($permData);
            $role->permissions()->attach($perm->permission_id);
        }

        $this->user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'staffuser',
            'password' => 'password123',
        ]);

        $this->accessToken = $response->json('access_token');
    }

    public function test_can_enroll_student_in_section(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section->section_id,
            ]);

        $response->assertStatus(201)
            ->assertJson(fn ($json) => $json->where('status', 'enrolled')->etc());

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);
    }

    public function test_cannot_enroll_in_full_section(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 1, 'current_enrollment' => 1]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section->section_id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_duplicate_enrollment(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);

        // First enrollment
        $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section->section_id,
            ]);

        // Duplicate enrollment
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section->section_id,
            ]);

        $response->assertStatus(409);
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

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson("/api/enrollments/{$enrollment->enrollment_id}/drop");

        $response->assertStatus(200)
            ->assertJson(fn ($json) => $json->where('status', 'dropped')->etc());
    }

    public function test_can_list_student_enrollments(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create();
        Enrollment::factory()->count(3)->create([
            'student_id' => $student->student_id,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->getJson("/api/students/{$student->student_id}/enrollments");

        $response->assertStatus(200);
    }

    public function test_schedule_conflict_prevents_enrollment(): void
    {
        $student = Student::factory()->create();

        // First section with Monday 9:00-10:30
        $section1 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section1->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        // Enroll in first section
        $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section1->section_id,
            ]);

        // Second section with overlapping Monday 10:00-11:30
        $section2 = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $section2->section_id,
            'day_of_week' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);

        // Should fail due to schedule conflict
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments', [
                'student_id' => $student->student_id,
                'section_id' => $section2->section_id,
            ]);

        $response->assertStatus(409);
    }

    public function test_unauthenticated_user_cannot_enroll(): void
    {
        $response = $this->postJson('/api/enrollments', [
            'student_id' => 1,
            'section_id' => 1,
        ]);

        $response->assertStatus(401);
    }
}
