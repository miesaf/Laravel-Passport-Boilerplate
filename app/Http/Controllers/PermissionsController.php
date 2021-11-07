<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;
use Artisan;

class PermissionsController extends Controller
{
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

}
