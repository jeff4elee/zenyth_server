<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App;


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

        $api_token = $request->header('Authorization');
        if($api_token == null)
            return response(json_encode(['error' => 'Unauthenticated']), 401);

        $user = User::where('api_token', $api_token)->first();
        if($user != null) {
            return $next($request);
        }

        return response(json_encode(['error' => 'Unauthenticated']), 401);

    }
}