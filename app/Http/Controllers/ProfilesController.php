<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use App\Models\PasswordHistory;
use App\Models\PasswordPolicy;
use Auth;
use Hash;
use Str;
use DB;

class ProfilesController extends Controller
{
    use ResponseTrait;

    public function changePassword(Request $request)
    {
        $validated = request()->validate([
            'old_password' => 'required',
            'new_password' => 'required'
        ]);

        if(!Hash::check($request->old_password, Auth::user()->password)) {
            return $this->failure("Old password is incorrect");
        }

        if($pword_policy = Controller::password_policy_check(Auth::user()->user_id, $request->new_password)) {
            return response()->json($pword_policy);
        }

        DB::beginTransaction();

        try {
            User::where('user_id', Auth::user()->user_id)->first()->update(['password' => bcrypt($validated['new_password']), 'is_force_change' => 0]);

            PasswordHistory::create([
                'user_id' => Auth::user()->user_id,
                'password' => bcrypt($validated['new_password'])
            ]);

            // Password cycle policy
            $policy = PasswordPolicy::find(16);
            if($policy->status) {
                $policy_value = $policy->value;
            } else {
                $policy_value = 1;
            }

            $keep = PasswordHistory::where('user_id', Auth::user()->user_id)->latest()->take($policy_value)->pluck('id');
            PasswordHistory::where('user_id', Auth::user()->user_id)->whereNotIn('id', $keep)->delete();

            DB::commit();

            return $this->success("Password changed successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
            return $this->failure("Password changed successfully but could not save to password history");
        }

        DB::rollBack();

        return $this->failure("Failed to update new password");
    }
}
