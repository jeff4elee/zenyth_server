<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\PasswordReset;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use Illuminate\Support\Facades\Hash;

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
        if( ! $token)
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 200);

        $validator = DataValidator::validateResetPassword($request);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }


        $password_reset = PasswordReset::where('token', '=', $token)->first();

        if($password_reset == null)
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 200);

        $user = User::where('email', '=', $password_reset->email)->first();


        $user->password = Hash::make($request['password']);
        $user->update();

        $password_reset->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully reset password'
        ], 200);

    }
}
