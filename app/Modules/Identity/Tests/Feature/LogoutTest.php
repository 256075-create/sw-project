<?php

namespace App\Modules\Identity\Tests\Feature;

use Tests\TestCase;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateUser(): array
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'logouttest',
            'email' => 'logouttest@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'logouttest',
            'password' => 'password123',
        ]);

        return [
            'user' => $user,
            'access_token' => $loginResponse->json('access_token'),
            'refresh_token' => $loginResponse->json('refresh_token'),
        ];
    }

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $auth = $this->authenticateUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['access_token'],
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    public function logout_revokes_all_refresh_tokens(): void
    {
        $auth = $this->authenticateUser();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['access_token'],
        ])->postJson('/api/auth/logout');

        $activeTokens = RefreshToken::where('user_id', $auth['user']->user_id)
            ->where('revoked', false)
            ->count();

        $this->assertEquals(0, $activeTokens);
    }

    /** @test */
    public function refresh_token_fails_after_logout(): void
    {
        $auth = $this->authenticateUser();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['access_token'],
        ])->postJson('/api/auth/logout');

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $auth['refresh_token'],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
