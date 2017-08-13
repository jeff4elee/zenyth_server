<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Http\Traits\SearchUserTrait;
use App\Repositories\RelationshipRepository;
use App\Repositories\UserRepository;
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

    private $relationshipRepo;
    private $userRepo;

    function __construct(RelationshipRepository $relationshipRepo,
                        UserRepository $userRepo)
    {
        $this->relationshipRepo = $relationshipRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Delete a user
     * @param Request $request
     * @param $username
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $username)
    {
        $user = $request->get('user');
        if ($username == $user->username) {
            $user->delete();
            return Response::successResponse(sprintf(DELETE_SUCCESS, USER));
        }

        Exceptions::unauthenticatedException(INVALID_TOKEN);
    }

    /**
     * Get user's friends
     * @param $user_id , id of user to be looked up
     * @return mixed Users who are friends of input user
     */
    public function getFriends($user_id)
    {
        $user = $this->userRepo->read($user_id);
        if ($user == null)
            Exceptions::notFoundException(INVALID_USER_ID);

        $friendsIds = $user->friendsId();
        $friends = $this->userRepo
            ->allUsersInIdArray($friendsIds)->all();

        return Response::dataResponse(true, ['users' => $friends]);
    }

    /**
     * Get blocked users of logged in user
     * @param Request $request , get request
     * @return mixed Users who are blocked by the logged in user
     */
    public function blockedUsers(Request $request)
    {
        $user = $request->get('user');
        $blockedUsersIds = $user->blockedUsersId();
        $blockedUsers = $this->userRepo
            ->allUsersInIdArray($blockedUsersIds)->all();

        return Response::dataResponse(true, ['users' => $blockedUsers]);
    }

    /**
     * Get friend requests of logged in user
     * @param Request $request , get request
     * @return mixed Users who friend requested the logged in user
     */
    public function getFriendRequests(Request $request)
    {
        $user = $request->get('user');

        $friendRequestsIds = $user->friendRequestsUsersId();
        $friendRequests = $this->userRepo->allUsersInIdArray($friendRequestsIds);

        return Response::dataResponse(true, ['users' => $friendRequests]);
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

            $query = $this->userRepo
                ->joinProfiles()->likeUsername($keyword)
                ->likeFirstName($keyword, true)->likeLastName($keyword, true)
                ->paginate(10);
            $users = $this->filterUserInfo($query->all());

            return Response::dataResponse(true, [
                'users' => $users
            ]);
        }

        $api_token = $request->header('Authorization');
        $api_token = $this->stripBearerFromToken($api_token);
        $user = null;
        // Validating the token since this route is not in the middleware
        if(!$api_token)
            Exceptions::invalidTokenException(INVALID_TOKEN);
        else
            $user = $this->userRepo->findBy('api_token', $api_token);

        if(!$user)
            Exceptions::invalidTokenException(INVALID_TOKEN);

        $keyword = strtolower($request->input('keyword'));
        $keyword = str_replace(" ", "%", $keyword);

        // All users' id's where username, first name, or last name are similar
        // to the keyword
        $allResultsId = $this->getRelevantResults($keyword, $user->id);

        // All users' id's where they are friends of this user
        $friendsId = $this->getAllFriendsId($user);

        // All users' id's where they are mutual friends of this user
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
        $searchResult = User::select('users.id', 'users.username')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->whereIn('users.id', $resultArr)
            ->orderByRaw('FIELD(users.id,'.$resultIdString.')')->paginate(10)
            ->all();

        $users = $this->filterUserInfo($searchResult);

        return Response::dataResponse(true, ['users' => $users]);
    }

    /**
     * Filter out unneeded information
     * @param $users
     * @return array
     */
    public function filterUserInfo($users)
    {
        foreach($users as $user) {
            // Filter out the information we don't need
            $user->makeHidden(['gender']);
            $user->makeHidden(['birthday']);
            $user->makeHidden(['friends']);
        }
        return $users;
    }

}