<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Contracts\IAuthorizationService;
use App\Modules\Identity\Models\User;

class AuthorizationService implements IAuthorizationService
{
    public function hasPermission(string $userId, string $permission): bool
    {
        $user = User::with(['roles.permissions'])->find($userId);

        if (!$user) {
            return false;
        }

        $userPermissions = $user->getAllPermissions();

        return in_array($permission, $userPermissions);
    }

    public function hasRole(string $userId, string $roleName): bool
    {
        $user = User::with('roles')->find($userId);

        if (!$user) {
            return false;
        }

        return $user->roles->contains('role_name', $roleName);
    }

    public function can(string $userId, string $resource, string $action): bool
    {
        $permission = "{$resource}.{$action}";
        return $this->hasPermission($userId, $permission);
    }
}
