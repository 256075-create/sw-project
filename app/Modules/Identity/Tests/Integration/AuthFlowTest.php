<?php

namespace App\Modules\Identity\Tests\Integration;

use Tests\TestCase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_auth_flow_login_to_protected_endpoint_to_logout(): void
    {
        // Setup: Create user with role and permissions
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'flowtest',
            'email' => 'flowtest@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Student', 'description' => 'Student role']);
        $perm = Permission::create([
            'permission_name' => 'courses.read',
            'resource' => 'courses',
            'action' => 'read',
        ]);
        $role->permissions()->attach($perm->permission_id);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        // Step 1: Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'flowtest',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $accessToken = $loginResponse->json('access_token');
        $refreshToken = $loginResponse->json('refresh_token');

        // Step 2: Access protected endpoint
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJsonFragment(['username' => 'flowtest']);

        // Step 3: Refresh token
        $refreshResponse = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse->assertStatus(200);
        $newAccessToken = $refreshResponse->json('access_token');
        $this->assertNotEmpty($newAccessToken);

        // Step 4: Use new token
        $meResponse2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newAccessToken,
        ])->getJson('/api/me');

        $meResponse2->assertStatus(200);

        // Step 5: Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newAccessToken,
        ])->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // Step 6: Verify refresh token is revoked after logout
        $failedRefresh = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $failedRefresh->assertStatus(401);
    }

    /** @test */
    public function permission_based_access_control_flow(): void
    {
        // Create user without admin permissions
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'student',
            'email' => 'student@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'StudentRole', 'description' => 'Student']);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'student',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Try to access admin-only endpoint (users list)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(403);
    }
}
