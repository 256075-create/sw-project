<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Contracts\IAuthenticationService;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Requests\RefreshTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected IAuthenticationService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('username'),
                $request->input('password')
            );

            return response()->json($result, 200);
        } catch (\InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'inactive') ? 403 : 401;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $userId = $request->input('auth_user')['user_id'] ?? null;

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->authService->logout($userId);

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->refresh(
                $request->input('refresh_token')
            );

            return response()->json($result, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
