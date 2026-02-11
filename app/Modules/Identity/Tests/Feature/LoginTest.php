<?php

namespace App\Modules\Identity\Tests\Feature;

use Tests\TestCase;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'johndoe',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'Bearer',
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials',
            ]);
    }

    /** @test */
    public function login_requires_username_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'inactive',
            'email' => 'inactive@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'inactive',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Account is inactive',
            ]);
    }

    /** @test */
    public function login_returns_valid_jwt_token(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'jwttest',
            'email' => 'jwttest@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'jwttest',
            'password' => 'password123',
        ]);

        $token = $response->json('access_token');
        $parts = explode('.', $token);
        $this->assertCount(3, $parts); // JWT has 3 parts
    }
}
