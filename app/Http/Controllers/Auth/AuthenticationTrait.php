<?php

namespace App\Http\Controllers\Auth;

use App\User;

trait AuthenticationTrait
{
    public function generateApiToken()
    {
        do {

            $api_token = "Bearer " . str_random(60);

            $dup_token_user = User::where('api_token', $api_token)
                ->first();

        } while ($dup_token_user != null);

        return $api_token;
    }
}