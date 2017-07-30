<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use App\PasswordReset;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Class ForgotPasswordController
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    use AuthenticationTrait;
    private $pwResetRepo;
    private $userRepo;

    /**
     * ForgotPasswordController constructor.
     */
    public function __construct(PasswordResetRepository $pwResetRepo,
                                UserRepository $userRepo)
    {
        $this->pwResetRepo = $pwResetRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Send the reset password email
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetPasswordEmail(Request $request)
    {
        if($request->has('email'))
            $user = $this->userRepo->findBy('email', $request['email']);
        else
            $user = $this->userRepo->findBy('username', $request['username']);

        // If there already is a password reset token for this user, resend the email with this token
        $passwordReset = $user->passwordReset;
        $email = $user->email;
        $name = $user->name();
        if($passwordReset)
            $token = $passwordReset->token;
        else { // Generate unique token
            do {
                $token = str_random(30);
                $dup_token = $this->pwResetRepo->findBy('token', $token);
            } while ($dup_token != null);

            // Create a password reset object with a token that is used to
            // validate the user's request to restore password
            $this->pwResetRepo->create(['email' => $email, 'token' => $token]);
        }

        // Send reset password email
        $subject = 'Reset your password';
        $infoArray = ['token' => $token];
        $this->sendEmail('restore_password_email', $infoArray, $email, $name, $subject);

        return Response::dataResponse(true, ['email' => $email],
            CHECK_EMAIL);
    }

    /**
     * Show the password reset html page
     * @param $token
     * @return $this|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function showPasswordResetBlade($token)
    {
        if($this->pwResetRepo->findBy('token', $token) == null)
            return Response::successResponse(INVALID_TOKEN, false);

        // Show the page for resetting password
        return view('restore_password_web')->with(['token' => $token]);
    }

    /**
     * Restore the user's password
     * @param Request $request
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function restorePassword(Request $request, $token)
    {
        if(!$token)
            return Response::successResponse(INVALID_TOKEN, false);

        // Validate the request to check for password and password_confirmation
        $validator = DataValidator::validateRestorePassword($request);
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        // Get the password reset object that associates with this token
        $passwordReset = $this->pwResetRepo->findBy('token', $token);
        if($passwordReset == null)
            return Response::successResponse(INVALID_TOKEN, false);

        // Get the user and change password of that user
        $user = $this->userRepo->findBy('email',$passwordReset->email);
        $user->password = Hash::make($request['password']);
        $user->update();
        $this->pwResetRepo->delete($passwordReset);

        return Response::successResponse(RESET_PW_SUCCESS);
    }
}
