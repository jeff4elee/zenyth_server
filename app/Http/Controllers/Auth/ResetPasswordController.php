<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\ResponseHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showPasswordResetBlade($token)
    {
        if(PasswordReset::where('token', '=', $token)->first() == null)
            return response(json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]), 200);

        return view('restore_password_web')->with(['token' => $token]);
    }

    public function restorePassword(Request $request, $token)
    {
        $exception = Exceptions::invalidTokenException();
        $jsonResponse = response()->json([
            'success' => false,
            'error' => [
                'type' => $exception->type,
                'code' => $exception->statusCode,
                'message' => $exception->message
            ]
        ]);
        if($token == null)
            return $jsonResponse;

        $validator = DataValidator::validateRestorePassword($request);
        if($validator->fails())
            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'InvalidParameterException',
                    'code' => 200,
                    'message' => ResponseHandler::formatErrors($validator)
                ]
            ]);

        $password_reset = PasswordReset::where('token', '=', $token)->first();

        if($password_reset == null)
            return $jsonResponse;

        $user = User::where('email', '=', $password_reset->email)->first();


        $user->password = Hash::make($request['password']);
        $user->update();

        $password_reset->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully reset password'
        ]);

    }
}
