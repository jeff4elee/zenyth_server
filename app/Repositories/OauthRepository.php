<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use App\Oauth;
use App\Http\Controllers\Auth\AuthenticationTrait;
use Illuminate\Http\Request;

class OauthRepository extends Repository
{
    function model()
    {
        return 'App\Oauth';
    }

    public function create(Request $request)
    {
        $user = $request->get('user');
        $oauth = Oauth::create(['user_id' => $user->id]);

        $oauth_type = strtolower($request['oauth_type']);
        if($oauth_type == 'google')
            $oauth->update(['google' => true]);
        else if($oauth_type == 'facebook')
            $oauth->update(['facebook' => true]);

        if($oauth)
            return $oauth;
        else
            Exceptions::unknownErrorException('Unable to create Oauth');
    }
}