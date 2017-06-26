<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use App\User;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * Gets user's friends
     *
     * @param $user_id, id of user to be looked up
     * @return mixed Users who are friends of input user
     */
    public function getFriends($user_id)
    {

        $searchResult = User::select('users.*')
            ->leftJoin('relationships', function ($join) use ($user_id) {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->where('relationships.requester', '=', $user_id)
                    ->orOn('users.id', '=', 'relationships.requester')
                    ->where('relationships.requestee', '=', $user_id);
            })
            ->where('relationships.status', '=', true)
            ->get();

        return $searchResult;

    }

    /**
     * Gets blocked users of logged in user
     *
     * @param Request $request, get request
     * @return mixed Users who are blocked by the logged in user
     */
    public function blockedUsers(Request $request)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        return User::select('users.*')
            ->leftJoin('relationships', function ($join) use ($user_id) {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->where('relationships.requester', '=', $user_id);
            })
            ->where('relationships.blocked', true)
            ->get();

    }

    /**
     * Gets friend requests of logged in user
     *
     * @param Request $request, get request
     * @return mixed Users who friend requested the logged in user
     */
    public function getFriendRequests(Request $request)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        return User::select('users.*')
            ->leftJoin('relationships', function ($join) use ($user_id) {
                $join->on('users.id', '=', 'relationships.requester')
                    ->where('relationships.requestee', '=', $user_id);
            })
            ->where('relationships.status', false)
            ->get();

    }

    /**
     * Searches users
     *
     * @param Request $request, get request
     * @param $name, name to be looked up
     * @return mixed users with similar results
     */
    public function searchUser(Request $request, $name)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $searchResult = User::select(['users.*', 'relationships.status'])
            ->leftJoin('relationships', function ($join) {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->orOn('users.id', '=', 'relationships.requester');
            })
            ->where([
                ['users.name', 'like', '%' . $name . '%'],
                ['users.id', '!=', $user_id]
            ])
            ->orderBy('relationships.status', 'desc')
            ->distinct('users.id')
            ->get();

        return $searchResult;

    }

}