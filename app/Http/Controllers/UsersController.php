<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use Auth;
use Str;

class UsersController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('users.list')) {
            return $this->forbidden();
        }

        if($users = User::orderBy('name')->get()) {
            return $this->successWithData("Success", $users);
        } else {
            return $this->failure("Failed to list users");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->can('users.add')) {
            return $this->forbidden();
        }

        // Logging into audit trail
        Controller::audit_log(Auth::user()->user_id, $request, "users.store");

        $validated = $request->validate([
            'user_id' => 'required|unique:users',
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'exists:roles,name',
            'permissions' => 'array|exists:permissions,name'
        ]);

        $validated['password'] = bcrypt(Str::random(8));

        if(User::create($validated)->assignRole($request->role)->givePermissionTo($request->permissions)) {
            return $this->success("User created successfully");
        } else {
            return $this->failure("Failed to create user");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!Auth::user()->can('users.view')) {
            return $this->forbidden();
        }

        if($user = User::with('roles', 'roles.permissions')->with('permissions')->find($id)) {
            return $this->successWithData("Success", $user);
        } else {
            return $this->failure("Failed to retrieve user");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!Auth::user()->can('users.update')) {
            return $this->forbidden();
        }

        // Logging into audit trail
        Controller::audit_log(Auth::user()->user_id, $request, "users.update");

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
            'user_id' => 'required|unique:users,user_id,' . $id,
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'exists:roles,name',
            'permissions' => 'array|exists:permissions,name'
        ]);

        if($user = User::find($id)) {
            $user->update($validated);
            $user->syncRoles($request->role);
            $user->syncPermissions($request->permissions);

            return $this->success("User updated successfully");
        } else {
            return $this->failure("Failed to update user");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if(!Auth::user()->can('users.delete')) {
            return $this->forbidden();
        }

        // Logging into audit trail
        Controller::audit_log(Auth::user()->user_id, $request, "users.delete");

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        if((User::find($id) != null) && User::find($id)->delete()) {
            return $this->success("User deleted successfully");
        } else {
            return $this->failure("Failed to delete user");
        }
    }
}
