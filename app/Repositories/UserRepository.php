<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use App\Http\Controllers\Auth\AuthenticationTrait;
use Carbon\Carbon;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository extends Repository
{
    use AuthenticationTrait;

    function model()
    {
        return 'App\User';
    }

    public function create(Request $request)
    {
        if($request->is('api/oauth/register')) {
            $password = Hash::make(str_random(16));
            $confirmation_code = null;
        }
        else {
            $password = Hash::make($request['password']);
            $confirmation_code = str_random(30);
        }

        $user = User::create([
            'email' => $request['email'],
            'username' => $request['username'],
            'password' => $password,
            'api_token' => $this->generateApiToken(),
            'token_expired_on' => Carbon::now()->addDays(365),
            'confirmation_code' => $confirmation_code
        ]);

        if($user)
            return $user;
        else
            Exceptions::unknownErrorException('Unable to create user');
    }

}