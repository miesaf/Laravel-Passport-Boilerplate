<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
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

}
