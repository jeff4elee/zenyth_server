<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Relationship;
use App\User;

class UserController extends Controller
{

    public function getFriends($user_id)
    {
        $friends_arr = [];
        $relationship1 = Relationship::where([
            ['requester', $user_id],
            ['status', true]
        ])->get();
        foreach($relationship1 as $relationship) {
            array_push($friends_arr, $relationship->getRequestee);
        }

        $relationship2 = Relationship::where([
            ['requestee', $user_id],
            ['status', true]
        ])->get();
        foreach($relationship2 as $relationship) {
            array_push($friends_arr, $relationship->getRequester);
        }

        return $friends_arr;

    }

    public function blockedUsers(Request $request)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        return Relationship::where([
            ['requester', $user_id],
            ['blocked', true]
        ])->get();

    }

    public function getFriendRequests(Request $request)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        return Relationship::where([
            ['requestee', $user_id],
            ['status', false]
        ])->get();

    }

    public function searchUser(Request $request, $name)
    {
        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $searchResult = DB::table('users')
            ->where([
                ['name', 'like', '%'.$name.'%'],
                ['users.id', '!=', $user_id]
            ]);

        $searchResult = $searchResult
            ->leftJoin('relationships', function($join)
            {
                $join->on('users.id', '=', 'relationships.requestee');
                $join->orOn('users.id', '=', 'relationships.requester');
            })->select('*')
            ->orderBy('relationships.status', 'desc')->get();

        return $searchResult;

    }

}