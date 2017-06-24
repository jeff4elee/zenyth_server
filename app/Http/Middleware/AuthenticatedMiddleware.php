<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class AuthenticatedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $api_token = $request['api_token'];
        $user = User::where('api_token', '=', $api_token)->first();
        if($user != null) {
            return $next($request);
        }
        return App::abort(401, 'Unauthenticated');
    }
}
