<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepository $roleRepository
    ) {}

    public function index(): JsonResponse
    {
        $roles = $this->roleRepository->all();
        return response()->json(['data' => $roles], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:50', 'unique:identity_roles,role_name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role = $this->roleRepository->create($data);
        return response()->json(['data' => $role], 201);
    }

    public function show(int $roleId): JsonResponse
    {
        $role = $this->roleRepository->findById($roleId);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return response()->json(['data' => $role], 200);
    }

    public function update(Request $request, int $roleId): JsonResponse
    {
        $data = $request->validate([
            'role_name' => ['sometimes', 'string', 'max:50', "unique:identity_roles,role_name,{$roleId},role_id"],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $role = $this->roleRepository->update($roleId, $data);
            return response()->json(['data' => $role], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $roleId): JsonResponse
    {
        $deleted = $this->roleRepository->delete($roleId);

        if (!$deleted) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return response()->json(['message' => 'Role deleted successfully'], 200);
    }

    public function assignPermission(Request $request, int $roleId): JsonResponse
    {
        $data = $request->validate([
            'permission_id' => ['required', 'integer', 'exists:identity_permissions,permission_id'],
        ]);

        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        if (!$role->permissions()->where('identity_permissions.permission_id', $data['permission_id'])->exists()) {
            $role->permissions()->attach($data['permission_id']);
        }

        return response()->json(['message' => 'Permission assigned successfully'], 200);
    }

    public function removePermission(int $roleId, int $permissionId): JsonResponse
    {
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $role->permissions()->detach($permissionId);

        return response()->json(['message' => 'Permission removed successfully'], 200);
    }
}
