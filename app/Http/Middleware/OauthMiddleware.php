<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Http\Requests\DataValidator;

class OauthMiddleware {
    use AuthenticationTrait;

    public function handle(Request $request, Closure $next)
    {

        // Invalid access token response
        $response = response(json_encode([
            'success' => false,
            'errors' => ['Invalid access token']
        ]), 401);

        $validator = DataValidator::validateOauthLogin($request);

        if($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $token = $request->header('Authorization');
        if($token != null) {
            $json = $this->oauthValidate($request);
            if($json == null) {
                return $response;
            }

            // If we don't have access to email
            if(!isset($json['email'])) {
                return response(json_encode([
                    'success' => true,
                    'data' => [
                        'email_access' => false,
                        'message' => 'No access to email'
                    ]
                ]), 200);
            }
            // If token is invalid or if email sent in the request is not the
            // same as one found from the token
            else if(isset($json['error']) || isset($json['error_description'])
                || $request['email'] != $json['email']) {
                return $response;
            }
            else {
                return $next($request);
            }

        } else {
            return $response;
        }

    }

}