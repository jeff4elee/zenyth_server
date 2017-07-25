<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Http\Traits\SearchUserTrait;
use App\User;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    use SearchUserTrait;
    use AuthenticationTrait;

    /**
     * Get user's friends
     * @param $user_id , id of user to be looked up
     * @return mixed Users who are friends of input user
     */
    public function getFriends($user_id)
    {

        $user = User::where('id', '=', $user_id);
        $friends_id = array_values($user->friendsId());
        if(count($friends_id) == 0)
            return Response::dataResponse(true, ['users' => null]);

        $searchResult = User::select('*')
            ->whereIn('id', $friends_id)->get();

        return Response::dataResponse(true, ['users' => $searchResult]);

    }

    /**
     * Get blocked users of logged in user
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

        return Response::dataResponse(true, ['users' => $searchResult]);

    }

    /**
     * Get friend requests of logged in user
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

        return Response::dataResponse(true, ['users' => $searchResult]);

    }

    /**
     * Search users
     * @param Request $request , get request
     * @return mixed users with similar results
     */
    public function searchUser(Request $request)
    {
        // If the request does not contain a header, search without any
        // result ranking
        if(!$request->hasHeader('Authorization')) {
            $keyword = strtolower($request->input('keyword'));
            $keyword = str_replace(" ", "%", $keyword);

            // This query contains all search results
            // but we need to filter by relevance
            $query = User::select('users.id', 'users.username', 'profiles.first_name',
                'profiles.last_name')
                ->join('profiles', 'profiles.user_id', '=', 'users.id')
                ->where('users.username', 'like', '%' . $keyword . '%')
                ->orWhere('profiles.first_name', 'like', '%' . $keyword . '%')
                ->orWhere('profiles.last_name', 'like', '%' . $keyword . '%');

            return Response::dataResponse(true, [
                'users' => $query->get()
            ]);
        }

        $api_token = $request->header('Authorization');
        $api_token = $this->stripBearerFromToken($api_token);
        $user = null;
        // Validating the token since this route is not in the middleware
        if(!$api_token)
            Exceptions::invalidTokenException('Invalid token');
        else
            $user = User::where('api_token', '=', $api_token)->first();
        if(!$user)
            Exceptions::invalidTokenException('Invalid token');

        $keyword = strtolower($request->input('keyword'));
        $keyword = str_replace(" ", "%", $keyword);

        $allResultsId = $this->getRelevantResults($keyword, $user->id);
        $friendsId = $this->getAllFriendsId($user);
        $mutualFriendsId = $this->getAllMutualFriendsId($friendsId);

        // Get the final result array containing the users' id in the order
        // that we want
        $resultArr = $this->inclusionExclusion($allResultsId, $friendsId,
                                                $mutualFriendsId);
        if (count($resultArr) == 0)
            return Response::dataResponse(true, [
                'users' => array()
            ]);

        // Joins the array with comma so we can do a raw query
        $resultIdString = implode(",", $resultArr);

        // The FIELD query returns the users in the same order of the id's
        // in the array
        $searchResult = User::select('users.id', 'users.username',
                        'profiles.first_name', 'profiles.last_name')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->whereIn('users.id', $resultArr)
            ->orderByRaw('FIELD(users.id,'.$resultIdString.')')->get();

        return Response::dataResponse(true, ['users' => $searchResult]);
    }

}