<?php

namespace App\Modules\Identity\Tests\Unit;

use Tests\TestCase;
use App\Modules\Identity\Services\AuthenticationService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthenticationService::class);
    }

    /** @test */
    public function it_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $result = $this->authService->login('testuser', 'password123');

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    /** @test */
    public function it_can_login_with_email(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'emailuser',
            'email' => 'emailuser@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $result = $this->authService->login('emailuser@example.com', 'password123');

        $this->assertArrayHasKey('access_token', $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_credentials(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent', 'wrongpassword');
    }

    /** @test */
    public function it_throws_exception_for_wrong_password(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'testuser2',
            'email' => 'test2@example.com',
            'password_hash' => Hash::make('correctpassword'),
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('testuser2', 'wrongpassword');
    }

    /** @test */
    public function it_throws_exception_for_inactive_user(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'inactive',
            'email' => 'inactive@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Account is inactive');

        $this->authService->login('inactive', 'password123');
    }

    /** @test */
    public function it_can_validate_valid_token(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'tokenuser',
            'email' => 'tokenuser@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $tokens = $this->authService->login('tokenuser', 'password123');
        $claims = $this->authService->validateToken($tokens['access_token']);

        $this->assertTrue($claims['valid']);
        $this->assertEquals($user->user_id, $claims['user_id']);
        $this->assertEquals('tokenuser', $claims['username']);
    }

    /** @test */
    public function it_throws_exception_for_invalid_token(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->authService->validateToken('invalid.token.here');
    }

    /** @test */
    public function it_can_refresh_token(): void
    {
        User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'refreshuser',
            'email' => 'refreshuser@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $tokens = $this->authService->login('refreshuser', 'password123');
        $this->travel(2)->seconds();
        $refreshed = $this->authService->refresh($tokens['refresh_token']);

        $this->assertArrayHasKey('access_token', $refreshed);
        $this->assertNotEquals($tokens['access_token'], $refreshed['access_token']);
        $this->assertEquals('Bearer', $refreshed['token_type']);
    }

    /** @test */
    public function it_throws_exception_for_invalid_refresh_token(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or expired refresh token');

        $this->authService->refresh('invalid-refresh-token');
    }

    /** @test */
    public function it_revokes_all_tokens_on_logout(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'logoutuser',
            'email' => 'logoutuser@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $tokens = $this->authService->login('logoutuser', 'password123');
        $this->authService->logout($user->user_id);

        $this->expectException(\InvalidArgumentException::class);
        $this->authService->refresh($tokens['refresh_token']);
    }

    /** @test */
    public function it_updates_last_login_on_successful_login(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'logintrack',
            'email' => 'logintrack@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->assertNull($user->last_login);

        $this->authService->login('logintrack', 'password123');
        $user->refresh();

        $this->assertNotNull($user->last_login);
    }

    /** @test */
    public function it_creates_refresh_token_in_database_on_login(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'dbtoken',
            'email' => 'dbtoken@example.com',
            'password_hash' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->assertEquals(0, RefreshToken::where('user_id', $user->user_id)->count());

        $this->authService->login('dbtoken', 'password123');

        $this->assertEquals(1, RefreshToken::where('user_id', $user->user_id)->count());
    }
}
