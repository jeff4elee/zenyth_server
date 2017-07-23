<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\User;
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

        $api_token = $request->header('Authorization');
        if($api_token == null)
            Exceptions::unauthenticatedException('This request requires an access token');

        $api_token = $this->stripBearerFromToken($api_token);

        if($api_token == null)
            Exceptions::invalidTokenException('Invalid access token');

        $user = User::where('api_token', $api_token)->first();

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