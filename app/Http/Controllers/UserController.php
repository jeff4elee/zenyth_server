<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Traits\SearchUserTrait;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    use SearchUserTrait;

    /**
     * Gets user's friends
     *
     * @param $user_id , id of user to be looked up
     * @return mixed Users who are friends of input user
     */
    public function getFriends($user_id)
    {

        $user = User::where('id', '=', $user_id);
        $friends_id = $user->friendsId();
        $searchResult = User::select('*')
            ->whereIn('id', $friends_id)->get();

        return response(json_encode([
            'success' => true,
            'data' => $searchResult
        ]), 200);

    }

    /**
     * Gets blocked users of logged in user
     *
     * @param Request $request , get request
     * @return mixed Users who are blocked by the logged in user
     */
    public function blockedUsers(Request $request)
    {

        $user = $request->get('user');
        $user_id = $user->id;

        $searchResult = User::select('users.*')
            ->leftJoin('relationships', function ($join) use ($user_id) {
                $join->on('users.id', '=', 'relationships.requestee')
                    ->where('relationships.requester', '=', $user_id);
            })
            ->where('relationships.blocked', true)
            ->get();

        return response(json_encode([
            'success' => true,
            'data' => $searchResult
        ]), 200);

    }

    /**
     * Gets friend requests of logged in user
     *
     * @param Request $request , get request
     * @return mixed Users who friend requested the logged in user
     */
    public function getFriendRequests(Request $request)
    {

        $user = $request->get('user');
        $user_id = $user->id;

        $searchResult = User::select('users.*')
            ->leftJoin('relationships', function ($join) use ($user_id) {
                $join->on('users.id', '=', 'relationships.requester')
                    ->where('relationships.requestee', '=', $user_id);
            })
            ->where('relationships.status', false)
            ->get();

        return response(json_encode([
            'success' => true,
            'data' => $searchResult
        ]), 200);

    }

    /**
     * Searches users
     *
     * @param Request $request , get request
     * @param $name , name to be looked up
     * @return mixed users with similar results
     */
    public function searchUser(Request $request, $name)
    {

        $api_token = $request->header('Authorization');
        $result_id = null;

        if($api_token == null) {

            $result_id = $this->similarTo(null, $name);

        } else {

            $user_id = User::where('api_token', $api_token)->first()->id;
            $result_id = $this->searchUserId($user_id, $name);

        }
        if (count($result_id) == 0)
            return array();

        $result_id_string = implode(",", $result_id);
        $searchResult = User::select('*')
            ->whereIn('id', $result_id)
            ->orderByRaw('FIELD(`id`,' . $result_id_string . ')')->get();

        return response(json_encode([
            'success' => true,
            'data' => $searchResult
        ]), 200);

    }

}