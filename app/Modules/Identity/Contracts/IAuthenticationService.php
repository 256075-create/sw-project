<?php

namespace App\Modules\Identity\Contracts;

interface IAuthenticationService
{
    /**
     * Authenticate user with credentials and return tokens.
     *
     * @param string $username
     * @param string $password
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function login(string $username, string $password): array;

    /**
     * Revoke all refresh tokens for the user.
     *
     * @param string $userId
     * @return void
     */
    public function logout(string $userId): void;

    /**
     * Issue a new access token using a valid refresh token.
     *
     * @param string $refreshToken
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function refresh(string $refreshToken): array;

    /**
     * Validate a JWT access token and return claims.
     *
     * @param string $token
     * @return array{valid: bool, user_id: string, username: string, roles: array, permissions: array}
     */
    public function validateToken(string $token): array;
}
