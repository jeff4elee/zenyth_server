<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use AuthenticationTrait;
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetPasswordEmail(Request $request)
    {
        if($request->has('email'))
            $user = User::where('email','=', $request['email'])->first();
        else
            $user = User::where('username','=', $request['username'])->first();

        // If there already is a password reset token for this user, resend the email with this token
        $passwordReset = $user->passwordReset;
        $email = $user->email;
        $name = $user->name();
        if($passwordReset)
            $token = $passwordReset->token;
        else { // Generate unique token
            do {
                $token = str_random(30);
                $dup_token = PasswordReset::where('token', '=', $token)->first();
            } while ($dup_token != null);

            PasswordReset::create(['email' => $email, 'token' => $token]);
        }

        $subject = 'Reset your password';
        $infoArray = ['token' => $token];
        $this->sendEmail('restore_password_email', $infoArray, $email, $name, $subject);

        return Response::dataResponse(true, ['email' => $email],
            'Check your email');
    }
}
