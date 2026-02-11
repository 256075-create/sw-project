<?php

namespace App\Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $authUser = $request->input('auth_user');

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userPermissions = $authUser['permissions'] ?? [];

        if (!in_array($permission, $userPermissions)) {
            return response()->json(['error' => 'Forbidden - Insufficient permissions'], 403);
        }

        return $next($request);
    }
}
