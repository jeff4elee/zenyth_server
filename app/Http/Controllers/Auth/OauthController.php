<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OauthController extends RegisterController
{
    use AuthenticationTrait;
    protected $facebookGraphApi = 'https://graph.facebook.com/me?fields=email,name&access_token=';

    public function oauthFBLogin(Request $request)
    {

        $access_token = $request->header('Authorization');
        $access_token = $this->stripBearerFromToken($access_token);

        $client = new Client();
        $res = $client->get($this->facebookGraphApi . $access_token);

        $json = json_decode($res->getBody()->getContents(), true);

        $email = $json['email'];

        $user = User::where('email', '=', $email)->first();
        if($user != null) {

            if($user->confirmation_code != null)
                return response(json_encode([
                    'success' => false,
                    'errors' => ['Account has not been confirmed']
                ]), 200);

            return response(json_encode([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'api_token' => $user->api_token
                ]
            ]), 200);

        } else { // Not supposed to happen
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid email']
            ]), 200);
        }

    }

}