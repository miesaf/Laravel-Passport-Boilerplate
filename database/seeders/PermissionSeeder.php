<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\PasswordHistory;
use Carbon\Carbon;

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

        // create 'Options' permissions
        Permission::create(['name' => 'options.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'options.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'options.add', 'guard_name' => 'api']);
        Permission::create(['name' => 'options.update', 'guard_name' => 'api']);

        // create 'Password Policy' permissions
        Permission::create(['name' => 'pwdPolicies.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'pwdPolicies.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'pwdPolicies.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'pwdPolicies.update', 'guard_name' => 'api']);
        Permission::create(['name' => 'pwdPolicies.toggle', 'guard_name' => 'api']);

        // create 'Users' permissions
        Permission::create(['name' => 'users.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.add', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.update', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.delete', 'guard_name' => 'api']);

        // create 'Audit Logs' permissions
        Permission::create(['name' => 'auditLogs.*', 'guard_name' => 'api']);
        Permission::create(['name' => 'auditLogs.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'auditLogs.view', 'guard_name' => 'api']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'Admin', 'guard_name' => 'api']);
        $role1->givePermissionTo('permissions.*');
        $role1->givePermissionTo('roles.*');
        $role1->givePermissionTo('users.*');
        $role1->givePermissionTo('options.*');
        $role1->givePermissionTo('pwdPolicies.*');
        $role1->givePermissionTo('auditLogs.*');

        $role2 = Role::create(['name' => 'Moderator', 'guard_name' => 'api']);
        $role2->givePermissionTo('users.list');
        $role2->givePermissionTo('users.view');

        $role3 = Role::create(['name' => 'User', 'guard_name' => 'api']);

        // create demo users
        $user = User::factory()->create([
            'user_id' => 'admin',
            'name' => 'Example Admin',
            'email' => 'admin@dimensikini.xyz',
            'password_created_at' => Carbon::now()
        ]);
        $user->assignRole($role1);
        PasswordHistory::factory()->create([
            'user_id' => $user->user_id,
            'password' => $user->password
        ]);

        $user = User::factory()->create([
            'user_id' => 'moderator',
            'name' => 'Example Moderator',
            'email' => 'moderator@dimensikini.xyz',
            'password_created_at' => Carbon::now()
        ]);
        $user->assignRole($role2);
        PasswordHistory::factory()->create([
            'user_id' => $user->user_id,
            'password' => $user->password
        ]);

        $user = User::factory()->create([
            'user_id' => 'user',
            'name' => 'Example User',
            'email' => 'user@dimensikini.xyz',
            'password_created_at' => Carbon::now()
        ]);
        $user->assignRole($role3);
        PasswordHistory::factory()->create([
            'user_id' => $user->user_id,
            'password' => $user->password
        ]);
    }
}
