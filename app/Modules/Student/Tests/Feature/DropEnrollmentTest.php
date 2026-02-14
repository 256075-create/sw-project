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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DropEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'dropuser',
            'email' => 'drop@test.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Staff', 'description' => 'Staff']);
        $perm = Permission::create([
            'permission_name' => 'enrollments.drop',
            'resource' => 'enrollments',
            'action' => 'drop',
        ]);
        $role->permissions()->attach($perm->permission_id);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'dropuser',
            'password' => 'password123',
        ]);

        $this->accessToken = $response->json('access_token');
    }

    public function test_can_drop_active_enrollment(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create(['max_capacity' => 30, 'current_enrollment' => 5]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'enrolled',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson("/api/enrollments/{$enrollment->enrollment_id}/drop");

        $response->assertStatus(200)
            ->assertJson(fn ($json) => $json->where('status', 'dropped')->etc());

        $section->refresh();
        $this->assertEquals(4, $section->current_enrollment);
    }

    public function test_cannot_drop_already_dropped_enrollment(): void
    {
        $student = Student::factory()->create();
        $section = Section::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'status' => 'dropped',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson("/api/enrollments/{$enrollment->enrollment_id}/drop");

        $response->assertStatus(422);
    }

    public function test_cannot_drop_nonexistent_enrollment(): void
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->postJson('/api/enrollments/99999/drop');

        $response->assertStatus(422);
    }
}
