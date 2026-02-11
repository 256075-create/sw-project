<?php

namespace App\Modules\Identity\Tests\Feature;

use Tests\TestCase;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_refresh_token(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'refreshtest',
            'email' => 'refreshtest@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'refreshtest',
            'password' => 'password123',
        ]);

        $refreshToken = $loginResponse->json('refresh_token');

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /** @test */
    public function refresh_fails_with_invalid_token(): void
    {
        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => 'invalid-token-string',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function refresh_requires_refresh_token_field(): void
    {
        $response = $this->postJson('/api/auth/refresh', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['refresh_token']);
    }

    /** @test */
    public function new_access_token_is_different_from_original(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'difftoken',
            'email' => 'difftoken@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'difftoken',
            'password' => 'password123',
        ]);

        $originalAccessToken = $loginResponse->json('access_token');

        sleep(1); // Ensure different iat

        $refreshResponse = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $loginResponse->json('refresh_token'),
        ]);

        $newAccessToken = $refreshResponse->json('access_token');

        $this->assertNotEquals($originalAccessToken, $newAccessToken);
    }
}
