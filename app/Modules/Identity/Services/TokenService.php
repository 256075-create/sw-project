<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\RefreshToken;
use Carbon\Carbon;

class TokenService
{
    /**
     * Revoke a specific refresh token.
     */
    public function revokeToken(string $tokenId): bool
    {
        $token = RefreshToken::find($tokenId);

        if (!$token) {
            return false;
        }

        $token->update(['revoked' => true]);

        return true;
    }

    /**
     * Revoke all refresh tokens for a user.
     */
    public function revokeAllUserTokens(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    /**
     * Clean up expired tokens from the database.
     */
    public function cleanExpiredTokens(): int
    {
        return RefreshToken::where('expires_at', '<', Carbon::now())
            ->orWhere('revoked', true)
            ->delete();
    }

    /**
     * Get active token count for a user.
     */
    public function getActiveTokenCount(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->count();
    }
}
