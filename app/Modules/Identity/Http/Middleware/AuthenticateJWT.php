<?php

namespace App\Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Identity\Contracts\IAuthenticationService;

class AuthenticateJWT
{
    public function __construct(
        protected IAuthenticationService $authService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $claims = $this->authService->validateToken($token);
            $request->merge(['auth_user' => $claims]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        return $next($request);
    }
}
