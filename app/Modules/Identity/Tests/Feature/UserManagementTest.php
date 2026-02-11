<?php

namespace App\Modules\Identity\Tests\Feature;

use Tests\TestCase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected string $adminToken;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAdminUser();
    }

    protected function setUpAdminUser(): void
    {
        $this->adminUser = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('admin12345'),
            'is_active' => true,
        ]);

        $adminRole = Role::create(['role_name' => 'Admin', 'description' => 'Administrator']);

        $permissions = [
            ['permission_name' => 'users.create', 'resource' => 'users', 'action' => 'create'],
            ['permission_name' => 'users.read', 'resource' => 'users', 'action' => 'read'],
            ['permission_name' => 'users.update', 'resource' => 'users', 'action' => 'update'],
            ['permission_name' => 'users.delete', 'resource' => 'users', 'action' => 'delete'],
        ];

        foreach ($permissions as $perm) {
            $p = Permission::create($perm);
            $adminRole->permissions()->attach($p->permission_id);
        }

        $this->adminUser->roles()->attach($adminRole->role_id, ['assigned_at' => now()]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin12345',
        ]);

        $this->adminToken = $loginResponse->json('access_token');
    }

    /** @test */
    public function admin_can_list_users(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_user(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['username' => 'newuser']);
    }

    /** @test */
    public function admin_can_view_user(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'viewme',
            'email' => 'viewme@example.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/users/{$user->user_id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'viewme']);
    }

    /** @test */
    public function admin_can_update_user(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'updateme',
            'email' => 'updateme@example.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/users/{$user->user_id}", [
            'username' => 'updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'updated']);
    }

    /** @test */
    public function authenticated_user_can_view_own_profile(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'admin']);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }
}
