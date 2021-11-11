<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForcePasswordChange
{   
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ctrlr = new Controller;

        // Check password expiry (to be moved to a middleware)
        if($account_dormant_check = $ctrlr->password_expiry_check(auth()->user()->user_id)) {
            return response()->json($account_dormant_check, 403);
        }

        // Check password force change (to be moved to a middleware)
        if(auth()->user()->is_force_change) {
            return response()->json(['status'=>false,'message'=>'Action blocked. You are required to change your password.'], 403);
        }

        return $next($request);
    }
}
