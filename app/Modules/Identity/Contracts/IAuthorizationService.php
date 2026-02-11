<?php

namespace App\Modules\Identity\Contracts;

interface IAuthorizationService
{
    /**
     * Check if a user has a specific permission.
     *
     * @param string $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $userId, string $permission): bool;

    /**
     * Check if a user has a specific role.
     *
     * @param string $userId
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $userId, string $roleName): bool;

    /**
     * Check if a user can perform an action on a resource.
     *
     * @param string $userId
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function can(string $userId, string $resource, string $action): bool;
}
