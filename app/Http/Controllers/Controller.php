<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password;
use Illuminate\Routing\Controller as BaseController;
use App\Models\PasswordPolicy;
use App\Models\PasswordHistory;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Hash;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function get_policies()
    {
        $getPolicies = PasswordPolicy::all();
        $policies = [];

        foreach ($getPolicies as $policy) {
            $policies[$policy->id] = $policy;
        }

        return $policies;
    }

    public function get_user($user_id)
    {
        return User::where('user_id', $user_id)->first();
    }

    public function login_attempt($user_id)
    {
        return User::where('user_id', $user_id)->first()->increment('failed_attempts', 1);
    }

    public function reset_login_attempt($user_id)
    {
        return User::where('user_id', $user_id)->first()->update(['failed_attempts' => 0]);
    }

    public function password_policy_check($user_id, $password)
    {
        $policies = $this->get_policies();
        $user = $this->get_user($user_id);

        // 1) Minumum length
        if ($policies[1]->status) {
            if(strlen($password) < $policies[1]->value) {
                return ['status'=>false, 'message'=>'New password must be at least '.$policies[1]->value.' characters long'];
            }
        }

        // 2) Maximum length
        if ($policies[2]->status) {
            if(strlen($password) > $policies[2]->value) {
                return ['status'=>false, 'message'=>'New password must not be more than '.$policies[1]->value.' characters long'];
            }
        }

        // 3) Alphanumeric
        if ($policies[3]->status) {
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);

            if((!$uppercase && !$lowercase) || !$number) {
                return ['status'=>false, 'message'=>'New password must contain both characters and numbers'];
            }
        }

        // 4) Multicase
        if ($policies[4]->status) {
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);

            if(!$uppercase || !$lowercase) {
                return ['status'=>false, 'message'=>'New password must contain both upper and lower case'];
            }
        }

        // 5) Special characters
        if ($policies[5]->status) {
            $validator = validator()->make(['password'=>$password], [
                'password' => ['required', Password::min(1)->symbols()],
            ]);

            if ($validator->fails()) {
                return ['status'=>false, 'message'=>'New password must contain a symbol/special character'];
            }
        }

        // 9) Minimum age (days)
        if ($policies[9]->status) {
            $now = Carbon::now();
            $password_created_at = Carbon::parse($user->password_created_at);

            if($now->diffInDays($password_created_at) < $policies[9]->value) {
                return ['status'=>false, 'message'=>'Your password has not passed minimum age of ' . $policies[9]->value . ' day(s). Password change rejected.'];
            }
        }

        // 12) Prevent reuse of password (cycles)
        if ($policies[12]->status) {
            $histories = PasswordHistory::where('user_id', $user_id)->latest()->take((int) $policies[12]->value - 1)->get();

            foreach ($histories as $history) {
                if(Hash::check($password, $history->password)) {
                    return ['status'=>false, 'message'=>'New password has been used before. Password was not changed.'];
                }
            }
        }

        // 13) Does not contain user's name
        if ($policies[13]->status) {
            $user = $this->get_user($user_id);
            $exploded_name = explode(" ",$user->name);

            foreach ($exploded_name as $name) {
                if (stripos($password, $name) !== false) {
                    return ['status'=>false, 'message'=>'New password cannot contain any of your name'];
                }
            }
        }

        // 14) Does not contain user's ID
        if ($policies[14]->status) {
            if (stripos($password, $user_id) !== false) {
                return ['status'=>false, 'message'=>'New password cannot contain your user ID'];
            }
        }

        // 15) Does not contain user's email
        if ($policies[15]->status) {
            $user = $this->get_user($user_id);
            $exploded_email = explode("@",$user->email);
            array_pop($exploded_email);
            $joined_email = join('@', $exploded_email);

            if (stripos($password, $joined_email) !== false) {
                return ['status'=>false, 'message'=>'New password cannot contain your email address local-part/username'];
            }
        }

        // 16) Compromised password check
        if ($policies[16]->status) {
            $validator = validator()->make(['password'=>$password], [
                'password' => ['required', Password::min(1)->uncompromised()],
            ]);

            if ($validator->fails()) {
                return ['status'=>false, 'message'=>'New password was considered compromised since it was listed in the haveibeenpwned.com database'];
            }
        }
    }

    public function password_expiry_check($user_id)
    {
        $policies = $this->get_policies();

        // 10) Maximum age (days)
        if ($policies[10]->status) {
            $now = Carbon::now();
            $user = $this->get_user($user_id);
            $last_login = Carbon::parse($user->password_created_at);

            if($now->diffInDays($last_login) > $policies[10]->value) {
                return ['status'=>false, 'message'=>'Action blocked because your password has expired. Please change to your new password.'];
            }
        }
    }

    public function pre_login_check($user_id)
    {
        $policies = $this->get_policies();

        // 6) Maximum failed attempts
        if ($policies[6]->status) {
            $user = $this->get_user($user_id);

            if($user->is_locked) {
                return ['status'=>false, 'message'=>'Your account was locked', 'locked'=>true];
            } else {
                if($user->failed_attempts >= $policies[6]->value) {
                    // 7) Lock on max failed attempts
                    if ($policies[7]->status) {
                        User::where('user_id', $user_id)->first()->update(['is_locked' => true]);
                        return ['status'=>false, 'message'=>'Your account was locked due to maximum number of failed login attempts'];
                    }
                }
            }
        }
    }

    public function post_login_check($user_id)
    {
        $policies = $this->get_policies();

        // 11) Dormant account (days)
        if ($policies[11]->status) {
            $now = Carbon::now();
            $user = $this->get_user($user_id);
            $last_login = Carbon::parse($user->current_signin);

            if($now->diffInDays($last_login) > $policies[11]->value) {
                User::where('user_id', $user_id)->first()->update(['is_active' => false]);
                return ['status'=>false, 'message'=>'Your account was deactivated due to being dormant for more than ' . $policies[11]->value . ' days.'];
            }
        }
    }

    public function audit_log($user_id, $vardata, $category)
    {
        $now = Carbon::now();
        $audit = new AuditLog;

        $audit->user_id = $user_id;
        $audit->req_time = $now;
        $audit->vardata = $vardata;
        $audit->category = $category;

        if(!$audit->save()) {
            // Do something if audit log logging failed
            // return $this->failure("Failed to delete option");
        }

        return true;
    }

    public function mask_value($vardata)
    {
        // get JSON start position
        $psn = stripos($vardata, '{');

        // get JSON
        $json = substr($vardata, $psn);

        // Decode JSON
        $jsonObj = json_decode($json);

        // Masking password
        if(isset($jsonObj->password)) {
            $jsonObj->password = "********";
        }

        if(isset($jsonObj->confirm_password)) {
            $jsonObj->confirm_password = "********";
        }

        if(isset($jsonObj->old_password)) {
            $jsonObj->old_password = "********";
        }

        if(isset($jsonObj->new_password)) {
            $jsonObj->new_password = "********";
        }

        // Encode back JSON
        $newJSON = json_encode($jsonObj);

        // Replace actual JSON with password masked JSON
        return str_replace($json, $newJSON, $vardata);
    }
}
