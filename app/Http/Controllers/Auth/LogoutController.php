<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;

class LogoutController extends Controller
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
     * Handle a logout attempt.
     *
     * @param Request $request, get request
     * @return Response json response indicating user is logged out
     */
    public function logout(Request $request)
    {

        $user = User::where('api_token', '=',
                            $request->header('Authorization'))->first();
        $user->api_token = null;
        $user->update();

        return response(json_encode(['logged out' => true])
                        , 202);

    }

}
