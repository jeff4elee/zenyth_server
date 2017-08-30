<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\RelationshipRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RelationshipController
 * @package App\Http\Controllers
 */
class RelationshipController extends Controller
{
    private $relationshipRepo;
    private $userRepo;

    function __construct(RelationshipRepository $relationshipRepo,
                        UserRepository $userRepo)
    {
        $this->relationshipRepo = $relationshipRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Send a follow request
     * @param Request $request, post request
     *        rules: requires requestee_id
     * @return JsonResponse
     */
    public function followRequest(Request $request)
    {
        $user = $request->get('user');
        $userId = $user->id;
        $requesteeId = (int)$request['requestee_id'];

        if($userId == $requesteeId)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship to check if user already made a follow request
        $relationship = $this->relationshipRepo
            ->getFollowRelationship($userId, $requesteeId)->all()->first();

        if ($relationship)
            Exceptions::invalidRequestException(EXISTED_RELATIONSHIP);

        $requestee = $this->userRepo->read($requesteeId);
        $userPrivacy = $requestee->userPrivacy;
        if ($userPrivacy->follow_privacy == 'public')
            $status = true;
        else
            $status = false;

        $request->merge([
            'requester' => $userId,
            'requestee' => $requesteeId,
            'status' => $status
        ]);

        $request = $request->except(['blocked']);

        $relationship = $this->relationshipRepo->create($request);

        return Response::dataResponse(true, ['relationship' => $relationship]);
    }

    /**
     * Respond to follower request
     * @param Request $request, post request
     *        rules: requires status with value true or false indicating
     *               whether request is accepted or not
     * @param $requester_id, person who friend requested
     * @return JsonResponse
     */
    public function respondToRequest(Request $request)
    {
        $user = $request->get('user');
        $requesteeId = $user->id;
        $requesterId = $request->input('requester_id');

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->getFollowRequest($requesterId, $requesteeId)->all()->first();

        if ($relationship == null || $relationship->status == true)
            Exceptions::invalidRequestException(NO_PENDING_REQUEST);

        // status from request indicating accept or ignore
        if ((bool)$request->input('status')) {
            $relationship->update(['status' => true]);
            return Response::dataResponse(true, ['relationship' => $relationship]);
        } else {
            $relationship->delete();
            return Response::successResponse(IGNORED_FOLLOWER_REQUEST);
        }
    }

    /**
     * Delete a follower. The deleter is the person being followed
     * @param Request $request, delete request
     * @param $follower_id, user to be deleted
     * @return JsonResponse
     */
    public function deleteFollower(Request $request, $follower_id)
    {
        $user = $request->get('user');
        $deleterId = $user->id;

        if ($this->userRepo->read($follower_id) == null)
            Exceptions::notFoundException(USER_DELETING_NOT_EXIST);

        // Cannot delete yourself
        if($deleterId == $follower_id)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->getFollowRelationship($follower_id, $deleterId)
            ->all()
            ->first();

        if ($relationship == null)
            Exceptions::notFoundException(sprintf(OBJECT_NOT_FOUND,
                RELATIONSHIP));

        if(!$relationship->blocked) {
            $relationship->delete();

            return Response::successResponse(sprintf(DELETE_SUCCESS,
                RELATIONSHIP));
        }
        else {
            Exceptions::invalidRequestException(USER_BLOCKED);
        }
    }

    /**
     * Unfollow a user. The person unfollowing is also the person following
     * the user. This request also cancels a follow request.
     * @param Request $request
     * @param $followee_id
     * @return JsonResponse
     */
    public function unfollow(Request $request, $followee_id)
    {
        $user = $request->get('user');
        $unfollowerId = $user->id;

        if ($this->userRepo->read($followee_id) == null)
            Exceptions::notFoundException(USER_DELETING_NOT_EXIST);

        // Cannot delete yourself
        if($unfollowerId == $followee_id)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->getFollowRelationship($unfollowerId, $followee_id)
            ->all()
            ->first();

        if ($relationship == null)
            Exceptions::notFoundException(sprintf(OBJECT_NOT_FOUND,
                RELATIONSHIP));

        if(!$relationship->blocked) {
            $relationship->delete();

            return Response::successResponse(sprintf(DELETE_SUCCESS,
                RELATIONSHIP));
        }
        else {
            Exceptions::invalidRequestException(USER_BLOCKED);
        }
    }

    /**
     * Block a user
     * @param Request $request, get request
     * @param $user_id, user to be blocked
     * @return JsonResponse
     */
    public function blockUser(Request $request)
    {
        $user = $request->get('user');
        $blockeeId = $request['user_id'];
        $blockerId = $user->id;

        // Cannot block yourself
        if($blockerId == $blockeeId)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->getRelationship($blockerId, $blockeeId)
            ->all()->first();

        if ($relationship) {
            if($relationship->blocked)
                Exceptions::invalidRequestException(EXISTED_RELATIONSHIP);

            $relationship->blocked = true;
            $relationship->status = false;
            $relationship->update();
        } else {
            $request->merge([
                'requester' => $blockerId,
                'requestee' => $blockeeId,
                'blocked' => true
            ]);
            $relationship = $this->relationshipRepo->create($request);
        }

        // Delete relationship blockeeId to blockerId
        $reverseRelationship = $this->relationshipRepo
            ->getRelationship($blockeeId, $blockerId)
            ->all()->first();
        if ($reverseRelationship)
            $reverseRelationship->delete();

        return Response::dataResponse(true, ['relationship' => $relationship]);
    }

}