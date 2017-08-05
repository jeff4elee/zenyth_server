<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Http\Controllers\Auth\AuthenticationTrait;
use Closure;
use Illuminate\Http\Request;


class AuthenticatedMiddleware
{
    use AuthenticationTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $this->getUserFromRequest($request);

        // Removes bearer from the front of api_token
        $request->headers->set('Authorization', $user->api_token);
        if($user != null) {
            // Inject the user object into the request in order for the
            // controllers to use it
            $request->merge(['user' => $user]);
            return $next($request);
        }

        Exceptions::unauthenticatedException();

    }
}