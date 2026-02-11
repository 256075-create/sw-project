<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Contracts\IUserService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService implements IUserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function create(array $data): User
    {
        $data['user_id'] = Str::uuid()->toString();
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        return $this->userRepository->create($data);
    }

    public function update(string $userId, array $data): User
    {
        if (isset($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        return $this->userRepository->update($userId, $data);
    }

    public function findById(string $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->list($filters, $perPage);
    }

    public function assignRole(string $userId, int $roleId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        if (!$user->roles()->where('identity_roles.role_id', $roleId)->exists()) {
            $user->roles()->attach($roleId, ['assigned_at' => now()]);
        }
    }

    public function removeRole(string $userId, int $roleId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->roles()->detach($roleId);
    }

    public function deactivate(string $userId): void
    {
        $this->userRepository->update($userId, ['is_active' => false]);
    }

    public function activate(string $userId): void
    {
        $this->userRepository->update($userId, ['is_active' => true]);
    }
}
