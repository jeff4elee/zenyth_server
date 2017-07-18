<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OauthController extends RegisterController
{
    use AuthenticationTrait;
    protected $facebookGraphApi = 'https://graph.facebook.com/me?fields=email,name&access_token=';
    protected $googleApi = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=';

    public function oauthLogin(Request $request)
    {

        $access_token = $request->header('Authorization');
        $access_token = $this->stripBearerFromToken($access_token);

        $client = new Client();

        $oauth_type = $request['oauth_type'];
        $res = null;

        if(strtolower($oauth_type) == "facebook")
            $res = $client->get($this->facebookGraphApi . $access_token);

        else if(strtolower($oauth_type) == "google")
            $res = $client->get($this->googleApi . $access_token);

        $json = json_decode($res->getBody()->getContents(), true);

        $email = $json['email'];

        $user = User::where('email', '=', $email)->first();
        if($user != null) {

            $oauth = $user->oauth;
            if($oauth->facebook || $oauth->google)
                return response(json_encode([
                    'success' => true,
                    'data' => [
                        'user' => $user,
                        'api_token' => $user->api_token
                    ]
                ]), 200);
            else {
                return response(json_encode([
                    'success' => false,
                    'errors' => ['An account with the same email has already been created'],
                    'can_merge' => true
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