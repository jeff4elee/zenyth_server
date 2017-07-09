<?php

namespace App\Http\Controllers\Auth;

use App\User;

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
        $api_token_arr = preg_split(" ", $api_token);

        if(strtolower($api_token_arr[0]) != "bearer" ||
            count($api_token_arr) != 2)
            return null;

        return $api_token_arr[1];
    }
}