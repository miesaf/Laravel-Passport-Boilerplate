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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        $permissions = Permission::all();

        return response()->json(['status' => true, 'count' => $permissions->count(), 'data' => $permissions]);
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
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        $validated = $request->validate([
            'name' => 'required|unique:permissions|max:255'
        ]);

        if(Permission::create($validated)) {
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
    public function destroy($id)
    {
        if(!Auth::user()->can('permissions.delete')) {
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        if(Permission::find($id)->delete()) {
            return $this->success("Permission deleted successfully");
        } else {
            return $this->failure("Failed to delete permission");
        }
    }
}
