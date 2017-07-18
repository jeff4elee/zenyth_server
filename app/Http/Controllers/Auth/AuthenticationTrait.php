<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;

trait AuthenticationTrait
{
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
        $access_token = $this->stripBearerFromToken($access_token);

        $client = new Client();

        $oauth_type = $request['oauth_type'];
        $res = null;

        if(strtolower($oauth_type) == "facebook")
            $res = $client->get($this->facebookGraphApi . $access_token);

        else if(strtolower($oauth_type) == "google")
            $res = $client->get($this->googleApi . $access_token);

        $json = json_decode($res->getBody()->getContents(), true);
        return $json;
    }
}