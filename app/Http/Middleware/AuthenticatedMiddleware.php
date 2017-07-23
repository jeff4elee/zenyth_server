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
            return Response::errorResponse(Exceptions::unauthenticatedException(),
                'request requires an access token');

        $api_token = $this->stripBearerFromToken($api_token);

        if($api_token == null)
            return Response::errorResponse(Exceptions::unauthenticatedException(),
                'invalid access token');

        $user = User::where('api_token', $api_token)->first();

        $request->headers->set('Authorization', $api_token);
        if($user != null) {
            $request->merge(['user' => $user]);
            return $next($request);
        }

        return Response::errorResponse(Exceptions::unauthenticatedException());

    }
}