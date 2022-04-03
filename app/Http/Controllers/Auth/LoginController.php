<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PasswordPolicy;
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

        // Logging into audit log
        $masked = Controller::mask_value($request);
        Controller::audit_log($request->user_id, $masked, "auth.login");

        if($user = User::where('user_id', $request->user_id)->first()) {
            // Check password exists (First time login)
            if(!$user->password) {
                return $this->failure("Please perform First Time Login", 200);
            }

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
                    return $this->failure("Your account was deactivated", 200);
                }

                if(Auth::user()->is_locked) {
                    return $this->failure("Your account was locked", 200);
                }

                if(($getTokenData = $this->getOauthTokenData($request->user_id, $request->password)) && isset($getTokenData->access_token)) {
                    // Log current login
                    Auth::user()->update([
                        'current_signin' => Carbon::now(),
                        'last_signin' => Auth::user()->current_signin
                    ]);

                    $userInfo = User::where('user_id', $request->user_id)->first();
                    $userInfo->getAllPermissions();

                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'token' => [
                            'token_type' => $getTokenData->token_type,
                            'expires_in' => $getTokenData->expires_in,
                            'expires_on' => Carbon::now()->add($getTokenData->expires_in . ' seconds'),
                            'refresh_expires_in' => (int) config('app.passport_refresh_tokens_expire_in') * 60,
                            'refresh_expires_on' => Carbon::now()->add((((int) config('app.passport_refresh_tokens_expire_in')) * 60) . ' seconds'),
                            'access_token' => $getTokenData->access_token,
                            'refresh_token' => $getTokenData->refresh_token
                        ],
                        'user' => $userInfo
                    ]);
                } else {
                    if(config('app.debug')) {
                        return response()->json([
                            'status'=>false,
                            'message'=>'Authentication error',
                            'debug'=>$getTokenData
                        ]);
                    } else {
                        return $this->failure("Authentication error", 200);
                    }
                }
            }
        } else {
            // User id not found. Throttle login increment
        }

        // 6) Maximum failed attempt
        $max_attempt = PasswordPolicy::find(6);

        if($max_attempt->status) {
            $max_attempt_val = (int) $max_attempt->value;

            // 8) Grace period on max failed attempts
            $grace = PasswordPolicy::find(8);
            $grace = $grace->status ? (int) $grace->value : 0;
        } else {
            $max_attempt_val = $grace = 0;
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid login credentials',
            'max_attempt' => $max_attempt_val,
            'grace' => $grace
        ]);
    }

    public function refreshToken(Request $request) {
        $validated = $request->validate([
            'refresh_token' => 'required'
        ]);

        $getNewTokenData = $this->refreshOauthToken($request->refresh_token);

        // split the token to get user_id
        $tokenParts = explode('.', $getNewTokenData->access_token);
        $payload = base64_decode($tokenParts[1]);
        
        // Logging into audit log
        Controller::audit_log(User::find(json_decode($payload)->sub)->user_id, $request, "auth.refreshtoken");

        return response()->json([
            'status'=>true,
            'message'=>'Token refreshed successfully',
            'token' => [
                'token_type' => $getNewTokenData->token_type,
                'expires_in' => $getNewTokenData->expires_in,
                'expires_on' => Carbon::now()->add($getNewTokenData->expires_in . ' seconds'),
                'refresh_expires_in' => (int) config('app.passport_refresh_tokens_expire_in') * 60,
                'refresh_expires_on' => Carbon::now()->add((((int) config('app.passport_refresh_tokens_expire_in')) * 60) . ' seconds'),
                'access_token' => $getNewTokenData->access_token,
                'refresh_token' => $getNewTokenData->refresh_token
            ],
        ]);
    }

    public function logout(Request $request) {
        try {
            $tokenRepository = app(TokenRepository::class);
            $refreshTokenRepository = app(RefreshTokenRepository::class);

            if($request->bearerToken()) {
                $token = $request->bearerToken();
                $jwt = explode(".", $token);
                $token_id = json_decode(base64_decode($jwt[1]))->jti;

                // Revoke an access token...
                $tokenRepository->revokeAccessToken($token_id);

                // Revoke all of the token's refresh tokens...
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token_id);
            }

            return $this->success("Logout successful");
        } catch (Throwable $e) {
            report($e);
            return $this->failed("Token revocation failed");
        }
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

    private function refreshOauthToken($refresh_token)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post(config('app.passport_login_endpoint'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id' => config('app.passport_client_id'),
                'client_secret' => config('app.passport_client_secret'),
                'scope' => ''
            ]);

            return json_decode($response->body());
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            if ($e->getCode() == 400) {
                return response()->json('Invalid Request. Please enter a refresh token.', $e->getCode());
            } else if ($e->getCode() == 401) {
                return response()->json('Your refresh token has expired. Please reauthenticate.', $e->getCode());
            }

            return response()->json('Something went wrong on the server.', $e->getCode());
        }
    }
}
