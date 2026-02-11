<?php

namespace App\Modules\Identity\Database\Factories;

use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'user_id' => Str::uuid()->toString(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
            'is_active' => true,
            'mfa_enabled' => false,
            'last_login' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withMfa(): static
    {
        return $this->state(fn (array $attributes) => [
            'mfa_enabled' => true,
        ]);
    }
}
