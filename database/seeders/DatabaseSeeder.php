<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Identity module seeders
            \App\Modules\Identity\Database\Seeders\RoleSeeder::class,
            \App\Modules\Identity\Database\Seeders\PermissionSeeder::class,
            \App\Modules\Identity\Database\Seeders\AdminUserSeeder::class,

            // Academic module seeders
            \App\Modules\Academic\Database\Seeders\AcademicSeeder::class,

            // Registration module seeders
            \App\Modules\Registration\Database\Seeders\RegistrationSeeder::class,

            // Student module seeders
            \App\Modules\Student\Database\Seeders\StudentSeeder::class,
        ]);
    }
}
