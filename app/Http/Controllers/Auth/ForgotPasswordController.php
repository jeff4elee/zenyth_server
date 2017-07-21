<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
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
        $validator = DataValidator::validateResetPasswordEmail($request);
        if($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        // Generate unique token
        do {
            $token = str_random(30);
            $dup_token = PasswordReset::where('token', '=', $token)->first();
        } while ($dup_token != null);

        $email = null;
        if($request->has('email')) {
            $email = $request['email'];
            PasswordReset::create([
                'email' => $email,
                'token' => $token
            ]);
        } else if($request->has('username')) {
            $email = User::where('username','=', $request['username'])->first()->email;
            PasswordReset::create([
                'email' => $email,
                'token' => $token
            ]);
        }

        Mail::send('restore_password_email', ['token' => $token]
            , function($message) use ($email) {
                $message->to($email, null)
                    ->subject('Reset your password');
            });

        return response(json_encode([
            'success' => true,
            'message' => 'Check your email',
            'data' => $email
        ]), 200);
    }
}
