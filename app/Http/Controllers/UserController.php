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

        $searchResult = User::
            select('users.*')
            ->leftJoin('relationships', function($join) use ($user_id)
            {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->where('relationships.requester','=', $user_id)
                    ->orOn('users.id', '=', 'relationships.requester')
                    ->where('relationships.requestee', '=', $user_id);
            })
            ->where('relationships.status', '=', true)
            ->get();

        return $searchResult;

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

        return User::
            select('users.*')
            ->leftJoin('relationships', function($join) use ($user_id)
            {
                $join->on('users.id', '=', 'relationships.requester')
                    ->where('relationships.requestee', '=', $user_id);
            })
            ->where('relationships.status', false)
            ->get();

    }

    public function searchUser(Request $request, $name)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $searchResult = User::
            select(['users.*', 'relationships.status'])
            ->leftJoin('relationships', function($join)
            {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->orOn('users.id', '=', 'relationships.requester');
            })
            ->where([
                ['users.name', 'like', '%'.$name.'%'],
                ['users.id', '!=', $user_id]
            ])
            ->orderBy('relationships.status', 'desc')
            ->distinct('users.id')
            ->get();

        return $searchResult;

    }

}