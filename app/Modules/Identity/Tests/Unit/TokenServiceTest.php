<?php

namespace App\Modules\Identity\Tests\Unit;

use Tests\TestCase;
use App\Modules\Identity\Services\TokenService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
    }

    protected function createUserWithToken(): array
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'tokentest' . Str::random(4),
            'email' => 'token' . Str::random(4) . '@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $token = RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', 'testtoken'),
            'expires_at' => Carbon::now()->addHours(24),
            'revoked' => false,
        ]);

        return [$user, $token];
    }

    /** @test */
    public function it_can_revoke_a_specific_token(): void
    {
        [$user, $token] = $this->createUserWithToken();

        $result = $this->tokenService->revokeToken($token->token_id);

        $this->assertTrue($result);
        $this->assertTrue($token->fresh()->revoked);
    }

    /** @test */
    public function it_returns_false_when_revoking_nonexistent_token(): void
    {
        $result = $this->tokenService->revokeToken(Str::uuid()->toString());

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_revoke_all_user_tokens(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'multitoken',
            'email' => 'multitoken@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        for ($i = 0; $i < 3; $i++) {
            RefreshToken::create([
                'token_id' => Str::uuid()->toString(),
                'user_id' => $user->user_id,
                'token_hash' => hash('sha256', "token{$i}"),
                'expires_at' => Carbon::now()->addHours(24),
                'revoked' => false,
            ]);
        }

        $count = $this->tokenService->revokeAllUserTokens($user->user_id);

        $this->assertEquals(3, $count);
        $this->assertEquals(0, RefreshToken::where('user_id', $user->user_id)->where('revoked', false)->count());
    }

    /** @test */
    public function it_can_clean_expired_tokens(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'expiredtest',
            'email' => 'expired@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Create expired token
        RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', 'expired'),
            'expires_at' => Carbon::now()->subHour(),
            'revoked' => false,
        ]);

        // Create valid token
        RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', 'valid'),
            'expires_at' => Carbon::now()->addHours(24),
            'revoked' => false,
        ]);

        $deleted = $this->tokenService->cleanExpiredTokens();

        $this->assertEquals(1, $deleted);
        $this->assertEquals(1, RefreshToken::where('user_id', $user->user_id)->count());
    }

    /** @test */
    public function it_counts_active_tokens_correctly(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'counttest',
            'email' => 'count@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', 'active1'),
            'expires_at' => Carbon::now()->addHours(24),
            'revoked' => false,
        ]);

        RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', 'revoked1'),
            'expires_at' => Carbon::now()->addHours(24),
            'revoked' => true,
        ]);

        $count = $this->tokenService->getActiveTokenCount($user->user_id);
        $this->assertEquals(1, $count);
    }
}
