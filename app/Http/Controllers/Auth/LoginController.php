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
                'errors' => $validator->errors()->all()
            ]), 400);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if ($user == null) {
            return response(json_encode([
                'errors' => ['Incorrect email or password']
            ]), 403);
        }

        if (Hash::check($password, $user->password)) {   // checks password
            // against hashed pw
            return response(json_encode(['login' => true]), 202);

        }

        return response(json_encode([
            'errors' => ['Incorrect email or password']
        ]), 403);

    }

}
