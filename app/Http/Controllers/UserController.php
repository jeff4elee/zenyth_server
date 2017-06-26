<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use App\User;
use App\Relationship;
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
     * @param Request $request , get request
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
     * @param Request $request , get request
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
     * @param Request $request , get request
     * @param $name , name to be looked up
     * @return mixed users with similar results
     */
    public function searchUser(Request $request, $name)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        /* Gets all the user id where names are similar */
        $similar_names_id = $this->similarTo($user_id, $name)->toArray();

        /* Gets all the user id where users are friends of logged in user */
        $friends_id = $this->getAllFriendsId($user_id)->toArray();

        /* Gets all the user id where users and mutual friends of logged
        in user */
        $mutual_friends_id = $this->getAllMutualFriendsId($user_id)->toArray();

        /* Retains only friends'id that have names similar to the name
        searched */
        $friends_id = array_filter($friends_id, function ($id)
        use ($similar_names_id) {
            return in_array($id, $similar_names_id);
        });

        /* Retains only mutual friends'id that have names similar to the name
         searched */
        $mutual_friends_id = array_filter($mutual_friends_id, function ($id)
        use ($similar_names_id) {
            return in_array($id, $similar_names_id);
        });

        /* Sorts the array in the order friends_id, mutual_friends_id, all
        the rest */
        $result_id = array_merge($friends_id, $mutual_friends_id);
        $rest_similar_names = array_diff($similar_names_id, $result_id);
        $result_id = array_merge($result_id, $rest_similar_names);

        $result_id_string = implode(",", $result_id);
        $searchResult = User::select('*')
            ->whereIn('id', $result_id)
            ->orderByRaw('FIELD(`id`,' . $result_id_string . ')')->get();

        return $searchResult;

    }

}