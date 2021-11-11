<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Mail\APIResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Mail;
use Str;
use DB;

class ForgotPasswordController extends Controller
{
    use ResponseTrait;

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required'
        ]);

        if($user = User::where('user_id', $request->user_id)->first()) {
            if(!$user->is_active) {
                return $this->failure("Your account was deactivated. Please contact system administrator to reactivate your account.");
            }

            // if($user->is_locked) {
            //     return $this->failure("Your account was locked. Please contact system administrator to unlock your account.");
            // }

            if(!$user->email) {
                return $this->failure("Email was not defined.");
            }

            $token = Str::random(60);

            DB::table('password_resets')->updateOrInsert(
                ['user_id' => $request->user_id],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            $reset = (object) array();

            $reset->link = config('app.url_fe') . "/passwords/reset/$token";
            $reset->appName = config('app.name');
            $reset->appURL = config('app.url_fe');
            $reset->userName = $user->name;

            Mail::to($user->email)->send(new APIResetPassword($reset));

            if(Mail::failures()) {
                return $this->failure("Failed to send reset link email.");
            } else {
                return $this->success("Please check your email for reset link.");
            }
        } else {
            return $this->failure("User ID not found");
        }
    }
}
