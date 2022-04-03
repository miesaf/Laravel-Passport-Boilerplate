<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\ResponseTrait;
use Auth;

class PermissionsController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('permissions.list')) {
            return $this->forbidden();
        }
        
        if($permissions = Permission::all()) {
            return $this->successWithData("Success", $permissions);
        } else {
            return $this->failure("Failed to list permissions");
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
        if(!Auth::user()->can('permissions.add')) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'name' => 'required|unique:permissions|max:255'
        ]);

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "permissions.store");

        if(Permission::create(['name'=>$request->name, 'guard_name'=>'api'])) {
            return $this->success("Permission created successfully");
        } else {
            return $this->failure("Failed to create permission");
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
        if(!Auth::user()->can('permissions.delete')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "permissions.destroy");

        if((Permission::find($id) != null) && Permission::find($id)->delete()) {
            return $this->success("Permission deleted successfully");
        } else {
            return $this->failure("Failed to delete permission");
        }
    }
}
