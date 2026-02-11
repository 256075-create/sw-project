<?php

namespace App\Modules\Identity\Repositories;

use App\Modules\Identity\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(int $roleId, array $data): Role
    {
        $role = $this->findById($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role not found');
        }

        $role->update($data);
        return $role->fresh();
    }

    public function findById(int $roleId): ?Role
    {
        return Role::with('permissions')->find($roleId);
    }

    public function findByName(string $roleName): ?Role
    {
        return Role::with('permissions')->where('role_name', $roleName)->first();
    }

    public function all(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function delete(int $roleId): bool
    {
        $role = $this->findById($roleId);
        if (!$role) {
            return false;
        }

        return $role->delete();
    }
}
