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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        $users = User::all();

        return response()->json(['status' => true, 'count' => $users->count(), 'data' => $users]);
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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        $validated = $request->validate([
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
    public function destroy($id)
    {
        if(!Auth::user()->can('users.delete')) {
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        if((User::find($id) != null) && User::find($id)->delete()) {
            return $this->success("User deleted successfully");
        } else {
            return $this->failure("Failed to delete user");
        }
    }
}
