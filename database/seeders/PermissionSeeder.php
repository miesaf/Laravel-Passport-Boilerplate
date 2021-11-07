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
        Permission::create(['name' => 'list permissions', 'guard_name' => 'api']);
        Permission::create(['name' => 'add permissions', 'guard_name' => 'api']);
        Permission::create(['name' => 'update permissions', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete permissions', 'guard_name' => 'api']);

        // create 'Roles' permissions
        Permission::create(['name' => 'list roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'add roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'view roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'update roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete roles', 'guard_name' => 'api']);

        // create 'Users' permissions
        Permission::create(['name' => 'list users', 'guard_name' => 'api']);
        Permission::create(['name' => 'add users', 'guard_name' => 'api']);
        Permission::create(['name' => 'view users', 'guard_name' => 'api']);
        Permission::create(['name' => 'update users', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete users', 'guard_name' => 'api']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $role2 = Role::create(['name' => 'moderator', 'guard_name' => 'api']);
        $role2->givePermissionTo('list users');
        $role2->givePermissionTo('view users');

        $role3 = Role::create(['name' => 'user', 'guard_name' => 'api']);
        $role3->givePermissionTo('list users');

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
