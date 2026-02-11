<?php

namespace App\Modules\Identity\Repositories;

use App\Modules\Identity\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $userId, array $data): User
    {
        $user = $this->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->update($data);
        return $user->fresh();
    }

    public function findById(string $userId): ?User
    {
        return User::with('roles')->find($userId);
    }

    public function findByUsername(string $username): ?User
    {
        return User::with('roles')->where('username', $username)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::with('roles')->where('email', $email)->first();
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function delete(string $userId): bool
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
