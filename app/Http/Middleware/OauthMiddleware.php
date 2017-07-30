<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Http\Requests\DataValidator;
use Closure;
use Illuminate\Http\Request;

class OauthMiddleware {
    use AuthenticationTrait;

    public function handle(Request $request, Closure $next)
    {

        $token = $request->header('Authorization');
        if($token != null) {
            $json = $this->oauthValidate($request);

            // If we don't have access to email
            if(!isset($json['email']))
                Exceptions::oauthException(OAUTH_NO_EMAIL_ACCESS);

            // If token is invalid or if email sent in the request is not the
            // same as one found from the token
            else if(isset($json['error']) || isset($json['error_description']))
                Exceptions::oauthException(INVALID_OAUTH_TOKEN);

            else if($request['email'] != $json['email'])
                Exceptions::oauthException(INVALID_OAUTH_EMAIL);

            else {
                $request->merge(['json' => $json]);
                return $next($request);
            }

        } else {
            Exceptions::oauthException(INVALID_OAUTH_TOKEN);
        }

    }

}