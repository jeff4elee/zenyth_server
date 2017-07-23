<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
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
            return Response::validatorErrorResponse($validator);

        $password = $request['password'];

        if($request->has('username'))
            $user = User::where('username', $request['username'])->first();
        else if($request->has('email'))
            $user = User::where('email', $request['email'])->first();
        else
            $user = null;


        if ($user == null) {
            return Response::errorResponse(Exceptions::invalidCredentialException(),
                'Incorrect email or password');
        }

        if (Hash::check($password, $user->password)) {   // checks password
            // against hashed pw

            if($user->confirmation_code != null)
                return Response::errorResponse(Exceptions::unconfirmedAccountException());
            else
                return Response::dataResponse(true, [
                    'user' => $user,
                    'api_token' => $user->api_token
                ], 'Successfully logged in');

        }

        return Response::errorResponse(Exceptions::invalidCredentialException(),
            'Incorrect email or password');

    }

}
