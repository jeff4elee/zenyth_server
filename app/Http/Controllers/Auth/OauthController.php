<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Requests\DataValidator;

class OauthController extends RegisterController
{
    use AuthenticationTrait;
    protected $facebookGraphApi = 'https://graph.facebook.com/me?fields=email,name&access_token=';
    protected $googleApi = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=';

    public function oauthLogin(Request $request)
    {
        $validator = DataValidator::validateOauthLogin($request);
        if($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        // Validates token
        $json = $this->oauthValidate($request);
        if($json == null || isset($json['error'])) {
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid access token']
            ]), 200);
        }
        $email = $json['email'];
        $oauth_type = $request['oauth_type'];

        $user = User::where('email', '=', $email)->first();
        if($user != null) {
            $oauth = $user->oauth;

            // Previously logged in with google but now logging in with facebook
            if(strtolower($oauth_type) == 'facebook' &&
                !$oauth->facebook && $oauth->google) {
                return response(json_encode([
                    'success' => false,
                    'errors' => ['An account with the same email has already been created'],
                    'data' => ['merge_google' => true]
                ]), 200);
            }
            // Previously logged in with facebook but now logging in with google
            else if(strtolower($oauth_type) == 'google' &&
                !$oauth->google && $oauth->facebook) {
                return response(json_encode([
                    'success' => false,
                    'errors' => ['An account with the same email has already been created'],
                    'data' => ['merge_facebook' => true]
                ]), 200);
            }

            // Previously created an account on the app but now logging in through oauth
            else if(!$oauth->facebook && !$oauth->google) {
                return response(json_encode([
                    'success' => false,
                    'errors' => ['An account with the same email has already been created'],
                    'data' => ['can_merge' => true]
                ]), 200);
            }
            else {
                return response(json_encode([
                    'success' => true,
                    'data' => [
                        'user' => $user,
                        'api_token' => $user->api_token,
                        'oauth_type' => $oauth_type
                    ]
                ]), 200);
            }


        } else { // Not supposed to happen
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid email']
            ]), 200);
        }

    }

}