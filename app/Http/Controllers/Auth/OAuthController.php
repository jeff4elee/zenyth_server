<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class OAuthController extends Controller
{

    public function oauthLogin()
    {
        $client = new Client();
        $res = $client->get('https://graph.facebook.com/me?fields=email,name&access_token=' .
            'EAAJcNklBh30BAKxeDaL7H1i7E0yCpDieF8rsRAg9X4La0xA5QmeIQ67F7sGVFYAhHZALHr3fGtmOZAskE42CU1OwoZCio4TaCGIjr13CaXVPZBCREYZAPnzOSCZAcoTBNgR3a3BlNVuy1jlSGgZBaepJVMM7QRil6cLhIh6TTbN57VZBVtp6WAtA8lnZBJ3F3HlYWZA62ZBRpMDMUC3c7Kp3HyUrZCc06H1ItbBi5CjZAVsgstQZDZD');

        return $res->getBody();
    }

}