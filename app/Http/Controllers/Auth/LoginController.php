<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Carbon\Carbon;
use App\Models\User;
use Auth;
use Http;

class LoginController extends Controller
{
    use ResponseTrait;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'user_id';
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'password' => 'required'
        ]);

        if($user = User::where('user_id', $request->user_id)->first()) {
            // Incremenet login count
            Controller::login_attempt($user->user_id);

            // Check account policy
            if($account_policy_check = Controller::pre_login_check($user->user_id)) {
                return response()->json($account_policy_check);
            }

            if(Auth::attempt($validated)) {
                // Reset login count
                Controller::reset_login_attempt($user->user_id);

                // Deactivate dormant account
                if($account_dormant_check = Controller::post_login_check($user->user_id)) {
                    return response()->json($account_dormant_check);
                }

                // Check all account flags
                if(!Auth::user()->is_active) {
                    return $this->failure("Your account was deactivated");
                }

                if(Auth::user()->is_locked) {
                    return $this->failure("Your account was locked");
                }

                if(($getTokenData = $this->getOauthTokenData($request->user_id, $request->password)) && isset($getTokenData->access_token)) {
                    // Log current login
                    Auth::user()->update([
                        'current_signin' => Carbon::now(),
                        'last_signin' => Auth::user()->current_signin
                    ]);

                    return response()->json([
                        'status'=>true,
                        'message'=>'Login successful',
                        'token'=>$getTokenData,
                        'user'=>User::where('user_id', $request->user_id)->first()
                    ]);
                } else {
                    return $this->failure("Authentication error");
                }
            }
        } else {
            // User id not found. Throttle login increment
        }

        return $this->failure("Invalid login credentials");
    }

    private function getOauthTokenData($username, $password)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post(config('app.passport_login_endpoint'), [
                'grant_type' => "password",
                'client_id' => config('app.passport_client_id'),
                'client_secret' => config('app.passport_client_secret'),
                'username' => $username,
                'password' => $password
            ]);

            return json_decode($response->body());
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            if ($e->getCode() == 400) {
                return response()->json('Invalid Request. Please enter a username or a password.', $e->getCode());
            } else if ($e->getCode() == 401) {
                return response()->json('Your credentials are incorrect. Please try again.', $e->getCode());
            }

            return response()->json('Something went wrong on the server.', $e->getCode());
        }
    }
}
