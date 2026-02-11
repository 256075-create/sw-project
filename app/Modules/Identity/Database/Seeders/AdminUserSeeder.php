<?php

namespace App\Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'user_id' => Str::uuid()->toString(),
                'username' => 'admin',
                'email' => 'admin@ums.edu',
                'password_hash' => Hash::make('Admin@123'),
                'is_active' => true,
                'mfa_enabled' => true,
            ]
        );

        $adminRole = Role::where('role_name', 'Admin')->first();
        if ($adminRole && !$admin->roles()->where('identity_roles.role_id', $adminRole->role_id)->exists()) {
            $admin->roles()->attach($adminRole->role_id, ['assigned_at' => now()]);
        }
    }
}
