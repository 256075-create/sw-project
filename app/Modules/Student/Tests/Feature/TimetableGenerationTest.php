<?php

namespace App\Modules\Student\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\Enrollment;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TimetableGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'timetableuser',
            'email' => 'timetable@test.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Student', 'description' => 'Student role']);
        $perm = Permission::create([
            'permission_name' => 'timetable.view',
            'resource' => 'timetable',
            'action' => 'view',
        ]);
        $role->permissions()->attach($perm->permission_id);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'timetableuser',
            'password' => 'password123',
        ]);

        $this->accessToken = $response->json('access_token');
    }

    public function test_can_view_student_timetable(): void
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

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->getJson("/api/students/{$student->student_id}/timetable");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['Monday']);
        $this->assertEquals('09:00', $data['Monday'][0]['start_time'] ?? $data['Monday'][0]['start_time']);
    }

    public function test_timetable_is_empty_for_student_with_no_enrollments(): void
    {
        $student = Student::factory()->create();

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->getJson("/api/students/{$student->student_id}/timetable");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $day => $slots) {
            $this->assertEmpty($slots, "Expected no slots on {$day}");
        }
    }

    public function test_timetable_excludes_dropped_enrollments(): void
    {
        $student = Student::factory()->create();

        // Active enrollment
        $activeSection = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 1]);
        Schedule::factory()->create([
            'section_id' => $activeSection->section_id,
            'day_of_week' => 'Tuesday',
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $activeSection->section_id,
            'status' => 'enrolled',
        ]);

        // Dropped enrollment
        $droppedSection = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 0]);
        Schedule::factory()->create([
            'section_id' => $droppedSection->section_id,
            'day_of_week' => 'Wednesday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $droppedSection->section_id,
            'status' => 'dropped',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->getJson("/api/students/{$student->student_id}/timetable");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data['Tuesday']);
        $this->assertEmpty($data['Wednesday']);
    }

    public function test_timetable_export_returns_summary(): void
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

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->getJson("/api/students/{$student->student_id}/timetable-export");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'student_id',
                    'total_courses',
                    'total_credits',
                    'courses',
                    'timetable',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_timetable(): void
    {
        $response = $this->getJson('/api/students/1/timetable');

        $response->assertStatus(401);
    }
}
