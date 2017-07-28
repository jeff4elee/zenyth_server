<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use App\Oauth;
use Illuminate\Http\Request;

class OauthRepository extends Repository
{
    function model()
    {
        return 'App\Oauth';
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function create(Request $request)
    {
        $user = $request->get('user');
        $oauth = $this->model->create(['user_id' => $user->id]);

        $oauth_type = strtolower($request['oauth_type']);
        if($oauth_type == 'google')
            $oauth->update(['google' => true]);
        else if($oauth_type == 'facebook')
            $oauth->update(['facebook' => true]);

        if($oauth)
            return $oauth;
        else
            Exceptions::unknownErrorException(OBJECT_FAIL_TO_CREATE);
    }
}