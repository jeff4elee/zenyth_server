<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OAuthController extends Controller
{

    public function oauthLogin(Request $request)
    {

        $access_token = $request->header('Authorization ');
        $client = new Client();
        $res = $client->get('https://graph.facebook.com/me?fields=email,name&access_token=' . $access_token);

        $json = json_decode($res->getBody()->getContents());

        return $json['email'];
    }

}