<?php

namespace App\Modules\Identity\Tests\Unit;

use Tests\TestCase;
use App\Modules\Identity\Services\AuthorizationService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthorizationService $authzService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authzService = new AuthorizationService();
    }

    /** @test */
    public function it_returns_true_when_user_has_permission(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'permuser',
            'email' => 'permuser@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'TestRole', 'description' => 'Test']);
        $permission = Permission::create([
            'permission_name' => 'test.read',
            'resource' => 'test',
            'action' => 'read',
        ]);

        $role->permissions()->attach($permission->permission_id);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $this->assertTrue($this->authzService->hasPermission($user->user_id, 'test.read'));
    }

    /** @test */
    public function it_returns_false_when_user_lacks_permission(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'noperm',
            'email' => 'noperm@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->assertFalse($this->authzService->hasPermission($user->user_id, 'test.write'));
    }

    /** @test */
    public function it_returns_false_for_nonexistent_user(): void
    {
        $this->assertFalse($this->authzService->hasPermission('nonexistent-uuid', 'test.read'));
    }

    /** @test */
    public function it_checks_role_correctly(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'roleuser',
            'email' => 'roleuser@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Admin', 'description' => 'Admin role']);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $this->assertTrue($this->authzService->hasRole($user->user_id, 'Admin'));
        $this->assertFalse($this->authzService->hasRole($user->user_id, 'Student'));
    }

    /** @test */
    public function it_checks_resource_action_permission(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'canuser',
            'email' => 'canuser@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $role = Role::create(['role_name' => 'Staff', 'description' => 'Staff role']);
        $permission = Permission::create([
            'permission_name' => 'courses.create',
            'resource' => 'courses',
            'action' => 'create',
        ]);

        $role->permissions()->attach($permission->permission_id);
        $user->roles()->attach($role->role_id, ['assigned_at' => now()]);

        $this->assertTrue($this->authzService->can($user->user_id, 'courses', 'create'));
        $this->assertFalse($this->authzService->can($user->user_id, 'courses', 'delete'));
    }

    /** @test */
    public function user_with_multiple_roles_has_combined_permissions(): void
    {
        $user = User::create([
            'user_id' => Str::uuid()->toString(),
            'username' => 'multirole',
            'email' => 'multirole@test.com',
            'password_hash' => Hash::make('password'),
            'is_active' => true,
        ]);

        $role1 = Role::create(['role_name' => 'Role1', 'description' => 'Role 1']);
        $role2 = Role::create(['role_name' => 'Role2', 'description' => 'Role 2']);

        $perm1 = Permission::create(['permission_name' => 'a.read', 'resource' => 'a', 'action' => 'read']);
        $perm2 = Permission::create(['permission_name' => 'b.write', 'resource' => 'b', 'action' => 'write']);

        $role1->permissions()->attach($perm1->permission_id);
        $role2->permissions()->attach($perm2->permission_id);

        $user->roles()->attach($role1->role_id, ['assigned_at' => now()]);
        $user->roles()->attach($role2->role_id, ['assigned_at' => now()]);

        $this->assertTrue($this->authzService->hasPermission($user->user_id, 'a.read'));
        $this->assertTrue($this->authzService->hasPermission($user->user_id, 'b.write'));
    }
}
