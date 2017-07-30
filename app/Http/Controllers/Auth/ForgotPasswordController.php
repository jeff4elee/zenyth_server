<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    use AuthenticationTrait;

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
            CHECK_EMAIL);
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
        if(!$token)
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 200);

        $validator = DataValidator::validateRestorePassword($request);
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
            'message' => RESET_PW_SUCCESS
        ], 200);

    }
}
