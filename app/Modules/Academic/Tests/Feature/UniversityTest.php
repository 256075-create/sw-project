<?php

namespace App\Modules\Academic\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Academic\Models\University;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Services\AuthenticationService;

class UniversityTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatedUser();
    }

    protected function setUpAuthenticatedUser(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::create(['role_name' => 'Admin', 'description' => 'Administrator']);
        $permission = Permission::create(['permission_name' => 'academic.read', 'resource' => 'academic', 'action' => 'read']);
        $createPerm = Permission::create(['permission_name' => 'academic.create', 'resource' => 'academic', 'action' => 'create']);
        $updatePerm = Permission::create(['permission_name' => 'academic.update', 'resource' => 'academic', 'action' => 'update']);
        $deletePerm = Permission::create(['permission_name' => 'academic.delete', 'resource' => 'academic', 'action' => 'delete']);
        $role->permissions()->attach([$permission->permission_id, $createPerm->permission_id, $updatePerm->permission_id, $deletePerm->permission_id]);
        $user->roles()->attach($role->role_id);

        $authService = app(AuthenticationService::class);
        $result = $authService->login($user->username, 'password');
        $this->token = $result['access_token'];
    }

    public function test_can_list_universities(): void
    {
        University::factory()->count(3)->create();

        $response = $this->getJson('/api/universities', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_create_university(): void
    {
        $response = $this->postJson('/api/universities', [
            'name' => 'Test University',
            'code' => 'TST',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('academic_universities', ['code' => 'TST']);
    }

    public function test_can_show_university(): void
    {
        $university = University::factory()->create();

        $response = $this->getJson("/api/universities/{$university->university_id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $university->name]);
    }

    public function test_can_update_university(): void
    {
        $university = University::factory()->create();

        $response = $this->putJson("/api/universities/{$university->university_id}", [
            'name' => 'Updated Name',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('academic_universities', ['name' => 'Updated Name']);
    }

    public function test_can_delete_university(): void
    {
        $university = University::factory()->create();

        $response = $this->deleteJson("/api/universities/{$university->university_id}", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('academic_universities', ['university_id' => $university->university_id]);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/universities');

        $response->assertStatus(401);
    }
}
