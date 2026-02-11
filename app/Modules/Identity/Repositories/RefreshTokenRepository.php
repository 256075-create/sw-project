<?php

namespace App\Modules\Identity\Repositories;

use App\Modules\Identity\Models\RefreshToken;
use Illuminate\Database\Eloquent\Collection;

class RefreshTokenRepository
{
    public function create(array $data): RefreshToken
    {
        return RefreshToken::create($data);
    }

    public function findByHash(string $tokenHash): ?RefreshToken
    {
        return RefreshToken::where('token_hash', $tokenHash)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revokeAllForUser(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    public function getActiveTokensForUser(string $userId): Collection
    {
        return RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->get();
    }
}
