<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use App\Models\PasswordPolicy;
use App\Models\PasswordHistory;
use Carbon\Carbon;
use Hash;
use DB;

class ResetPasswordController extends Controller
{
    use ResponseTrait;

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function reset(Request $request)
    {
        // Validate token existance/string validity
        if($token = DB::table('password_resets')->where('token', $request->token)->first()) {
            // Logging into audit log
            $masked = Controller::mask_value($request);
            Controller::audit_log($token->user_id, $masked, "auth.resetpwd");

            $T_now = Carbon::now();
            $T_created = Carbon::createFromTimeString($token->created_at);

            // Validate token time frame validity
            if($T_now->diffInMinutes($T_created) < 60) {
                // Check User ID exixtance
                if($user = DB::table('users')->where('user_id', $token->user_id)->first()) {
                    // Validate password policy
                    if($pword_policy = Controller::password_policy_check($user->user_id, $request->new_password)) {
                        return response()->json($pword_policy);
                    }

                    DB::beginTransaction();

                    try {
                        User::where('user_id', $token->user_id)
                            ->first()
                            ->update([
                                'password' => Hash::make($request->new_password),
                                'password_created_at' => Carbon::now(),
                                'is_force_change' => 0,
                                'failed_attempts' => 0
                            ]);

                        PasswordHistory::create([
                            'user_id' => $user->user_id,
                            'password' => bcrypt($request->new_password)
                        ]);

                        // Password cycle policy
                        $policy = PasswordPolicy::find(16);
                        if($policy->status) {
                            $policy_value = $policy->value;
                        } else {
                            $policy_value = 1;
                        }

                        $keep = PasswordHistory::where('user_id', $user->user_id)->latest()->take($policy_value)->pluck('id');
                        PasswordHistory::where('user_id', $user->user_id)->whereNotIn('id', $keep)->delete();

                        DB::table('password_resets')->where('token', $request->token)->delete();

                        DB::commit();

                        return $this->success("Password changed successfully");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return $this->failure("Failed to save new password.");
                    }

                    DB::rollBack();

                    $err_msg = "Failed to save new password.";
                } else {
                    $err_msg = "User ID not found.";
                }
            } else {
                $err_msg = "Reset password link has expired.";
            }
        } else {
            $err_msg = "Reset password link is invalid.";
        }

        return $this->failure($err_msg);
    }
}
