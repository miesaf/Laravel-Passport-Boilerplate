<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('roles.list')) {
            return response()->json(['status' => false, 'message' => "Forbidden request due to insufficient permission"]);
        }

        $roles = Role::all();

        return response()->json(['status' => true, 'count' => $roles->count(), 'data' => $roles]);
    }

}
