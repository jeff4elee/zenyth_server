<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Exceptions;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Trait AuthenticationTrait
 * @package App\Http\Controllers\Auth
 */
trait AuthenticationTrait
{

    protected $facebookGraphApi = 'https://graph.facebook.com/me?fields=email,first_name,last_name,gender,picture.type(large)&access_token=';
    protected $googleApi = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=';

    /**
     * Generate a unique api token
     * @return string
     */
    public function generateApiToken()
    {
        do {

            $api_token = str_random(60);
            $dup_token_user = User::where('api_token', $api_token)
                ->first();

        } while ($dup_token_user != null);

        return $api_token;
    }

    /**
     * Strips bearer from api token
     *
     * @param $api_token
     * @return null
     */
    public function stripBearerFromToken($api_token)
    {
        $api_token_arr = explode(" ", $api_token);

        // If the token does not start with bearer and if the array does not
        // contain 2 strings then return null
        if (strtolower($api_token_arr[0]) != "bearer" ||
            count($api_token_arr) != 2)
            return null;

        return $api_token_arr[1];
    }

    /**
     * Validates oauth request
     *
     * @param Request $request
     * @return mixed|null
     */
    public function oauthValidate(Request $request)
    {
        $access_token = $request->header('Authorization');
        if ($access_token == null) {
            Exceptions::oauthException(INVALID_TOKEN);
        }
        $access_token = $this->stripBearerFromToken($access_token);

        // Make the oauth_type case insensitive
        $oauth_type = strtolower($request['oauth_type']);

        // Create a client for making a request to facebook/google api to
        // validate the access token
        $client = new Client();
        $res = null;

        if ($oauth_type == "facebook")
            $res = $client->get($this->facebookGraphApi . $access_token);
        else if ($oauth_type == "google")
            $res = $client->get($this->googleApi . $access_token);
        
        if ($res == null)
            Exceptions::invalidRequestException(REQUEST_NO_RESPONSE);

        // Get json response back in an array
        $json = json_decode($res->getBody()->getContents(), true);
        return $json;

    }

    /**
     * Send email
     * @param $blade
     * @param $infoArray
     * @param $email
     * @param $name
     * @param $subject
     */
    public function sendEmail($blade, $infoArray, $email, $name, $subject)
    {
        Mail::send($blade, $infoArray
            , function ($message) use ($email, $name, $subject) {
                $message->to($email, $name)
                    ->subject($subject);
            });
    }

}