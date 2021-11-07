<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create 'Permissions' permissions
        Permission::create(['name' => 'permissions.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'permissions.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'permissions.add', 'guard_name' => 'api']);
        Permission::create(['name' => 'permissions.delete', 'guard_name' => 'api']);

        // create 'Roles' permissions
        Permission::create(['name' => 'roles.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles.add', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles.update', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles.delete', 'guard_name' => 'api']);

        // create 'Users' permissions
        Permission::create(['name' => 'users.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.add', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.update', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.delete', 'guard_name' => 'api']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider
        $role1->givePermissionTo('permissions.*');
        $role1->givePermissionTo('roles.*');
        $role1->givePermissionTo('users.*');

        $role2 = Role::create(['name' => 'moderator', 'guard_name' => 'api']);
        $role2->givePermissionTo('users.list');
        $role2->givePermissionTo('users.view');

        $role3 = Role::create(['name' => 'user', 'guard_name' => 'api']);
        $role3->givePermissionTo('users.list');

        // create demo users
        $user = User::factory()->create([
            'user_id' => 'admin',
            'name' => 'Example Admin',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole($role1);

        $user = User::factory()->create([
            'user_id' => 'moderator',
            'name' => 'Example Moderator',
            'email' => 'moderator@example.com',
        ]);
        $user->assignRole($role2);

        $user = User::factory()->create([
            'user_id' => 'user',
            'name' => 'Example User',
            'email' => 'user@example.com',
        ]);
        $user->assignRole($role3);
    }
}
