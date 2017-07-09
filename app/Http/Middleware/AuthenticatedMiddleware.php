<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App;
use App\Http\Controllers\Auth\AuthenticationTrait;


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
    public function handle($request, Closure $next)
    {

        $api_token = $request->header('Authorization');
        if($api_token == null)
            return response(json_encode([
                'success' => false,
                'errors' => ['Unauthenticated']
            ]), 401);

        $api_token = $this->stripBearerFromToken($api_token);

        if($api_token == null)
            return response(json_encode([
                'success' => false,
                'errors' => ['Unauthenticated']
            ]), 401);

        $user = User::where('api_token', $api_token)->first();

        $request->headers->set('Authorization', $api_token);
        if($user != null) {
            return $next($request);
        }

        return response(json_encode([
            'success' => false,
            'errors' => ['Unauthenticated']
        ]), 401);

    }
}