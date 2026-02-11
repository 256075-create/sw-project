<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Contracts\IAuthenticationService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\RefreshToken;
use App\Modules\Identity\Events\UserLoggedIn;
use App\Modules\Identity\Events\UserLoggedOut;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthenticationService implements IAuthenticationService
{
    public function login(string $username, string $password): array
    {
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password_hash)) {
            Log::warning('Failed login attempt', ['username' => $username]);
            throw new \InvalidArgumentException('Invalid credentials');
        }

        if (!$user->is_active) {
            Log::warning('Inactive user login attempt', ['user_id' => $user->user_id]);
            throw new \InvalidArgumentException('Account is inactive');
        }

        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        $user->update(['last_login' => now()]);

        event(new UserLoggedIn($user));

        Log::info('User logged in', ['user_id' => $user->user_id]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('identity.jwt.access_token_ttl') * 60,
        ];
    }

    public function logout(string $userId): void
    {
        RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->update(['revoked' => true]);

        $user = User::find($userId);
        if ($user) {
            event(new UserLoggedOut($user));
        }

        Log::info('User logged out', ['user_id' => $userId]);
    }

    public function refresh(string $refreshToken): array
    {
        $tokenHash = hash('sha256', $refreshToken);

        $token = RefreshToken::where('token_hash', $tokenHash)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            throw new \InvalidArgumentException('Invalid or expired refresh token');
        }

        $user = User::find($token->user_id);

        if (!$user || !$user->is_active) {
            throw new \InvalidArgumentException('User account is not available');
        }

        $accessToken = $this->generateAccessToken($user);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('identity.jwt.access_token_ttl') * 60,
        ];
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(config('identity.jwt.secret'), config('identity.jwt.algo'))
            );

            return [
                'valid' => true,
                'user_id' => $decoded->sub,
                'username' => $decoded->username,
                'roles' => $decoded->roles,
                'permissions' => $decoded->permissions,
            ];
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid token: ' . $e->getMessage());
        }
    }

    protected function generateAccessToken(User $user): string
    {
        $user->load('roles.permissions');

        $payload = [
            'iss' => config('identity.jwt.issuer'),
            'aud' => config('identity.jwt.audience'),
            'iat' => time(),
            'exp' => time() + (config('identity.jwt.access_token_ttl') * 60),
            'sub' => $user->user_id,
            'username' => $user->username,
            'roles' => $user->roles->pluck('role_name')->toArray(),
            'permissions' => $user->getAllPermissions(),
        ];

        return JWT::encode($payload, config('identity.jwt.secret'), config('identity.jwt.algo'));
    }

    protected function generateRefreshToken(User $user): string
    {
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        RefreshToken::create([
            'token_id' => Str::uuid()->toString(),
            'user_id' => $user->user_id,
            'token_hash' => $tokenHash,
            'expires_at' => Carbon::now()->addMinutes((int) config('identity.jwt.refresh_token_ttl')),
            'revoked' => false,
        ]);

        return $token;
    }
}
