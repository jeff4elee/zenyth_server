<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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

        $email = $request['email'];
        $password = $request['password'];

        $user = User::where('email', '=', $email)->first();

        if($user == null){
          return 0;
        }

        if(password_verify($password, $user->password)){

            // Authentication passed...
            do{

                $api_token = str_random(60);

                $dup_token_user = User::where('api_token', '=', $api_token)
                    ->first();

            } while( $dup_token_user != null );

            //found unique api token

            $user->api_token = $api_token;
            $user->update();

            return json_encode(['api_token' => $api_token]);

        }

            return 0;

        }

}
