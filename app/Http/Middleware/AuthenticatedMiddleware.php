<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Repositories\UserRepository;
use App\User;
use Closure;
use Illuminate\Http\Request;


class AuthenticatedMiddleware
{
    use AuthenticationTrait;
    private $userRepo;

    function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $api_token = $request->header('Authorization');
        if($api_token == null)
            Exceptions::unauthenticatedException(REQUIRES_ACCESS_TOKEN);

        $api_token = $this->stripBearerFromToken($api_token);

        if($api_token == null)
            Exceptions::invalidTokenException(INVALID_TOKEN);

        $user = $this->userRepo->findBy('api_token', $api_token);

        // Removes bearer from the front of api_token
        $request->headers->set('Authorization', $api_token);
        if($user != null) {
            // Inject the user object into the request in order for the
            // controllers to use it
            $request->merge(['user' => $user]);
            return $next($request);
        }

        Exceptions::unauthenticatedException();

    }
}