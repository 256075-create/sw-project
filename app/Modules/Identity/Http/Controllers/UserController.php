<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Contracts\IUserService;
use App\Modules\Identity\Http\Requests\RegisterRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected IUserService $userService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = $request->input('per_page', 15);

        $users = $this->userService->list($filters, $perPage);

        return UserResource::collection($users);
    }

    public function store(RegisterRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json(new UserResource($user), 201);
    }

    public function show(string $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(new UserResource($user), 200);
    }

    public function update(Request $request, string $userId): JsonResponse
    {
        $data = $request->validate([
            'username' => ['sometimes', 'string', 'max:100', "unique:identity_users,username,{$userId},user_id"],
            'email' => ['sometimes', 'email', 'max:200', "unique:identity_users,email,{$userId},user_id"],
            'password' => ['sometimes', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'mfa_enabled' => ['sometimes', 'boolean'],
        ]);

        try {
            $user = $this->userService->update($userId, $data);
            return response()->json(new UserResource($user), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function assignRole(Request $request, string $userId): JsonResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:identity_roles,role_id'],
        ]);

        try {
            $this->userService->assignRole($userId, $data['role_id']);
            return response()->json(['message' => 'Role assigned successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function removeRole(Request $request, string $userId, int $roleId): JsonResponse
    {
        try {
            $this->userService->removeRole($userId, $roleId);
            return response()->json(['message' => 'Role removed successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $authUser = $request->input('auth_user');
        $user = $this->userService->findById($authUser['user_id']);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(new UserResource($user), 200);
    }
}
