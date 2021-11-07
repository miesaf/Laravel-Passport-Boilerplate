<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Http;

class LoginController extends Controller
{
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
        $credentials = [
            'user_id' => $request->user_id,
            'password' => $request->password
        ];

        if(Auth::attempt($credentials)) {
            $user = User::find(Auth::user()->id);
            $getTokenData = $this->getOauthTokenData($request->user_id, $request->password);

            return response()->json($getTokenData);
        } else {
            return response()->json(['message'=>'Invalid login credentials'], 401);
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

            return  json_decode($response->body());
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
