<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use Illuminate\Support\Facades\Validator;
use App\Relationship;
use App\User;

class UserController extends Controller
{

    public function getFriends(Request $request)
    {
        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $friends_arr = [];

        $relationship1 = Relationship::where('requester', $user_id)->all();

        $relationship2 = Relationship::where('requestee', $user_id)->all();

        array_push($friends_arr, $relationship1);
        array_push($friends_arr, $relationship2);

        return $friends_arr;

    }

}