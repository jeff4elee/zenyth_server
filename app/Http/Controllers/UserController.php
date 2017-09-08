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
     * Get user's followers
     * @param $user_id , id of user to be looked up
     * @return mixed Users who are followers of input user
     */
    public function getFollowers($user_id)
    {
        $user = $this->userRepo->read($user_id);

        if ($user == null)
            Exceptions::notFoundException(INVALID_USER_ID);

        $followerIds = $user->followerIds();
        $followers = $this->userRepo
            ->allUsersInIdArray($followerIds)->all();

        return Response::dataResponse(true, ['users' => $followers]);
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
     * Get follow requests of logged in user
     * @param Request $request , get request
     * @return mixed Users who follow requested the logged in user
     */
    public function getFollowerRequests(Request $request)
    {
        $user = $request->get('user');

        $followerRequestsIds = $user->followRequestsUsersIds();

        $followerRequests = $this->userRepo->allUsersInIdArray
            ($followerRequestsIds)->all();

        return Response::dataResponse(true, ['users' => $followerRequests]);
    }

    /**
     * Get relationship between two users
     * @param $requestee_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFollowerStatus(Request $request, $requestee_id)
    {
        $user = $request->get('user');
        $requesterId = $user->id;
        $relationship = $this->relationshipRepo->getRelationship($requesterId,
            $requestee_id)->all()->first();
        return Response::dataResponse(true, ['relationship' => $relationship]);
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

        // All users' id's where they are followers of this user
        $followerIds = $this->getAllFollowerIds($user);

        $followingIds = $this->getAllFollowingIds($user);

        // Get the final result array containing the users' id in the order
        // that we want
        $resultArr = $this->inclusionExclusion($allResultsId, $followingIds,
                                                $followerIds);
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
            ->orderByRaw('FIELD(users.id,'.$resultIdString.')')->simplePaginate
            (10);

        $this->filterUserInfo($searchResult);
        $users = $searchResult->toArray();
        $users['users'] = $users['data'];
        unset($users['data']);

        $nextPageUrl = $users['next_page_url'];
        $prevPageUrl = $users['prev_page_url'];

        // Adding back the keyword as path query
        if ($nextPageUrl)
            $users['next_page_url'] = $nextPageUrl . '&keyword=' . $keyword;
        if ($prevPageUrl)
            $users['prev_page_url'] = $prevPageUrl . '&keyword=' . $keyword;

        return Response::dataResponse(true, $users);
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
            $user->makeHidden(['followers']);
        }
        return $users;
    }

}
