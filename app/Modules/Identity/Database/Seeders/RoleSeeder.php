<?php

namespace App\Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Admin', 'description' => 'Full system access, manage users/roles, configure SSO'],
            ['role_name' => 'Registration Staff', 'description' => 'Manage courses, manage sections, view student list'],
            ['role_name' => 'Student', 'description' => 'Enroll in courses, view timetable, view own profile'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }
    }
}
