<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Http\Requests\DataValidator;

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
    use AuthenticationTrait;

    /**
     * Handle an authentication attempt.
     *
     * @param Request $request, post request,
     *        rules: requires email and password
     * @return Response json response with api_token or json response
     *         indicating login failed
     */
    public function login(Request $request)
    {
        $validator = DataValidator::validateLogin($request);
        if ($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $user = null;
        $password = $request['password'];
        $user = null;

        if($request->has('username'))
            $user = User::where('username', $request['username'])->first();
        else if($request->has('email'))
            $user = User::where('email', $request['email'])->first();


        if ($user == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['Incorrect email or password']
            ]), 200);
        }

        if (Hash::check($password, $user->password)) {   // checks password
            // against hashed pw

            if($user->confirmation_code != null)
                return response(json_encode([
                    'success' => false,
                    'errors' => ['Account has not been confirmed']
                ]), 200);
            else
                return response(json_encode([
                    'success' => true,
                    'data' => [
                        'user' => $user,
                        'api_token' => $user->api_token
                    ]
                ]), 200);

        }

        return response(json_encode([
            'success' => false,
            'errors' => ['Incorrect email or password']
        ]), 200);

    }

}
