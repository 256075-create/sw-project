<?php

namespace App\Modules\Identity\Contracts;

use App\Modules\Identity\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface IUserService
{
    public function create(array $data): User;
    public function update(string $userId, array $data): User;
    public function findById(string $userId): ?User;
    public function findByUsername(string $username): ?User;
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function assignRole(string $userId, int $roleId): void;
    public function removeRole(string $userId, int $roleId): void;
    public function deactivate(string $userId): void;
    public function activate(string $userId): void;
}
