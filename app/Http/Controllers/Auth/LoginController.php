<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
     * Handle an authentication attempt.
     *
     * @return Response
     */
    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $email, 'password' => $password])) {

          // Authentication passed...
          do{

            $api_token = str_random(60);

            $user = User::where('api_token', '=', $api_token)->first();

          } while( $user != null );

          //found unique api token
          $user = User::where('email', '=', $email)->first();
          $user->api_token = $apiToken;
          $user->update();

          return json_encode(['api_token' => $api_token]);

        }

        return 0;

    }

}
