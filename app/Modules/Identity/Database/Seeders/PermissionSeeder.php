<?php

namespace App\Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User management
            ['permission_name' => 'users.create', 'resource' => 'users', 'action' => 'create'],
            ['permission_name' => 'users.read', 'resource' => 'users', 'action' => 'read'],
            ['permission_name' => 'users.update', 'resource' => 'users', 'action' => 'update'],
            ['permission_name' => 'users.delete', 'resource' => 'users', 'action' => 'delete'],

            // Role management
            ['permission_name' => 'roles.create', 'resource' => 'roles', 'action' => 'create'],
            ['permission_name' => 'roles.read', 'resource' => 'roles', 'action' => 'read'],
            ['permission_name' => 'roles.update', 'resource' => 'roles', 'action' => 'update'],
            ['permission_name' => 'roles.delete', 'resource' => 'roles', 'action' => 'delete'],

            // Course management
            ['permission_name' => 'courses.create', 'resource' => 'courses', 'action' => 'create'],
            ['permission_name' => 'courses.read', 'resource' => 'courses', 'action' => 'read'],
            ['permission_name' => 'courses.update', 'resource' => 'courses', 'action' => 'update'],
            ['permission_name' => 'courses.delete', 'resource' => 'courses', 'action' => 'delete'],

            // Section management
            ['permission_name' => 'sections.create', 'resource' => 'sections', 'action' => 'create'],
            ['permission_name' => 'sections.read', 'resource' => 'sections', 'action' => 'read'],
            ['permission_name' => 'sections.update', 'resource' => 'sections', 'action' => 'update'],
            ['permission_name' => 'sections.delete', 'resource' => 'sections', 'action' => 'delete'],

            // Student management
            ['permission_name' => 'students.create', 'resource' => 'students', 'action' => 'create'],
            ['permission_name' => 'students.read', 'resource' => 'students', 'action' => 'read'],
            ['permission_name' => 'students.update', 'resource' => 'students', 'action' => 'update'],
            ['permission_name' => 'students.delete', 'resource' => 'students', 'action' => 'delete'],

            // Enrollment
            ['permission_name' => 'enrollments.enroll', 'resource' => 'enrollments', 'action' => 'enroll'],
            ['permission_name' => 'enrollments.drop', 'resource' => 'enrollments', 'action' => 'drop'],
            ['permission_name' => 'enrollments.read', 'resource' => 'enrollments', 'action' => 'read'],

            // Timetable
            ['permission_name' => 'timetable.view', 'resource' => 'timetable', 'action' => 'view'],

            // Academic structure
            ['permission_name' => 'academic.create', 'resource' => 'academic', 'action' => 'create'],
            ['permission_name' => 'academic.read', 'resource' => 'academic', 'action' => 'read'],
            ['permission_name' => 'academic.update', 'resource' => 'academic', 'action' => 'update'],
            ['permission_name' => 'academic.delete', 'resource' => 'academic', 'action' => 'delete'],

            // Classrooms
            ['permission_name' => 'classrooms.create', 'resource' => 'classrooms', 'action' => 'create'],
            ['permission_name' => 'classrooms.read', 'resource' => 'classrooms', 'action' => 'read'],
            ['permission_name' => 'classrooms.update', 'resource' => 'classrooms', 'action' => 'update'],
            ['permission_name' => 'classrooms.delete', 'resource' => 'classrooms', 'action' => 'delete'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['resource' => $perm['resource'], 'action' => $perm['action']],
                $perm
            );
        }

        // Assign permissions to roles
        $this->assignAdminPermissions();
        $this->assignRegistrationStaffPermissions();
        $this->assignStudentPermissions();
    }

    protected function assignAdminPermissions(): void
    {
        $admin = Role::where('role_name', 'Admin')->first();
        if ($admin) {
            $allPermissions = Permission::pluck('permission_id')->toArray();
            $admin->permissions()->syncWithoutDetaching($allPermissions);
        }
    }

    protected function assignRegistrationStaffPermissions(): void
    {
        $staff = Role::where('role_name', 'Registration Staff')->first();
        if ($staff) {
            $permissions = Permission::whereIn('permission_name', [
                'courses.create', 'courses.read', 'courses.update', 'courses.delete',
                'sections.create', 'sections.read', 'sections.update', 'sections.delete',
                'students.read',
                'enrollments.read',
                'classrooms.create', 'classrooms.read', 'classrooms.update', 'classrooms.delete',
                'academic.read',
            ])->pluck('permission_id')->toArray();
            $staff->permissions()->syncWithoutDetaching($permissions);
        }
    }

    protected function assignStudentPermissions(): void
    {
        $student = Role::where('role_name', 'Student')->first();
        if ($student) {
            $permissions = Permission::whereIn('permission_name', [
                'enrollments.enroll', 'enrollments.drop', 'enrollments.read',
                'timetable.view',
                'courses.read',
                'sections.read',
                'academic.read',
            ])->pluck('permission_id')->toArray();
            $student->permissions()->syncWithoutDetaching($permissions);
        }
    }
}
