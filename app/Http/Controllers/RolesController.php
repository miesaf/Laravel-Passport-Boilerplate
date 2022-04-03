<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\ResponseTrait;
use Auth;

class RolesController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('roles.list')) {
            return $this->forbidden();
        }
        
        if($roles = Role::with('permissions')->get()) {
            return $this->successWithData("Success", $roles);
        } else {
            return $this->failure("Failed to list roles");
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
        if(!Auth::user()->can('roles.add')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "roles.store");

        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
            'permissions' => 'array|exists:permissions,name'
        ]);

        if(Role::create(['name'=>$request->name, 'guard_name'=>'api'])->givePermissionTo($request->permissions)) {
            return $this->success("Role created successfully");
        } else {
            return $this->failure("Failed to create role");
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
        if(!Auth::user()->can('roles.view')) {
            return $this->forbidden();
        }

        if($role = Role::with('permissions')->find($id)) {
            return $this->successWithData("Success", $role);
        } else {
            return $this->failure("Failed to retrieve role");
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
        if(!Auth::user()->can('roles.update')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "roles.update");

        $validated = $request->validate([
            'permissions' => 'array|exists:permissions,name'
        ]);

        if(Role::find($id)->syncPermissions($request->permissions)) {
            return $this->success("Role updated successfully");
        } else {
            return $this->failure("Failed to update role");
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
        if(!Auth::user()->can('roles.delete')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "roles.delete");

        if((Role::find($id) != null) && Role::find($id)->delete()) {
            return $this->success("Role deleted successfully");
        } else {
            return $this->failure("Failed to delete role");
        }
    }
}
