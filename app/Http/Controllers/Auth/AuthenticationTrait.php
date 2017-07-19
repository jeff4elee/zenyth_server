<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

//include_once __DIR__ . '../../../../vendor/autoload.php';
require_once __DIR__.'../../../../vendor/autoload.php';

trait AuthenticationTrait
{

    protected $facebookGraphApi = 'https://graph.facebook.com/me?fields=email,name&access_token=';
    protected $googleApi = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=';
    protected $CLIENT_ID = '894303575310-9dqkdbua8pq2bajm24s7hob9fuibd1eb.apps.googleusercontent.com';

    public function generateApiToken()
    {
        do {

            $api_token = str_random(60);

            $dup_token_user = User::where('api_token', $api_token)
                ->first();

        } while ($dup_token_user != null);

        return $api_token;
    }

    public function stripBearerFromToken($api_token) {
        $api_token_arr = explode(" ", $api_token);

        if(strtolower($api_token_arr[0]) != "bearer" ||
            count($api_token_arr) != 2)
            return null;

        return $api_token_arr[1];
    }

    public function oauthValidate(Request $request) {
        $access_token = $request->header('Authorization');
        if($access_token == null) {
            return null;
        }
        $access_token = $this->stripBearerFromToken($access_token);

        $client = new Client();

        $oauth_type = strtolower($request['oauth_type']);
        $res = null;

        if($oauth_type == "facebook") {
            $res = $client->get($this->facebookGraphApi . $access_token);
            if($res == null) {
                return null;
            }
            $json = json_decode($res->getBody()->getContents(), true);
            return $json;
        }

        else if($oauth_type == "google") {
            //$res = $client->get($this->googleApi . $access_token);

            $client = new \Google_Client([
                'client_id' => $this->CLIENT_ID
            ]);
            $client->setScopes('email');
            $payload = $client->verifyIdToken($access_token); // $access_token being the idToken form google
            return $payload;
        }

    }
}