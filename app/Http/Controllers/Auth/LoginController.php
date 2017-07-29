<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticationTrait;
    protected $userRepo;

    function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Handle an authentication attempt.
     * @param Request $request, post request,
     *        rules: requires email and password
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $password = $request['password'];

        if($username = $request->input('username'))
            $user = $this->userRepo->findBy('username', $username);
        else if($email = $request->input('email'))
            $user = $this->userRepo->findBy('email', $email);
        else
            $user = null;

        if ($user == null)
            Exceptions::invalidCredentialException(LOGIN_INVALID_CONFIDENTIAL);

        if (Hash::check($password, $user->password)) {
            // checks password against hashed pw

            return Response::dataResponse(true, [
                'user' => $user->makeVisible('api_token')
            ]);
        }

        Exceptions::invalidCredentialException(LOGIN_INVALID_CONFIDENTIAL);
    }

}
