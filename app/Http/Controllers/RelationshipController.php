<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\RelationshipRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RelationshipController
 * @package App\Http\Controllers
 */
class RelationshipController extends Controller
{
    private $relationshipRepo;

    function __construct(RelationshipRepository $relationshipRepo)
    {
        $this->relationshipRepo = $relationshipRepo;
    }

    /**
     * Send a friend request
     * @param Request $request, post request
     *        rules: requires requestee_id
     * @return JsonResponse
     */
    public function friendRequest(Request $request)
    {
        $user = $request->get('user');
        $userId = $user->id;
        $requesteeId = (int)$request['requestee_id'];

        if($userId == $requesteeId)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->hasRelationship($userId, $requesteeId)->all()->first();

        if ($relationship)
            Exceptions::invalidRequestException(EXISTED_RELATIONSHIP);

        $request->merge([
            'requester' => $userId,
            'requestee' => $requesteeId
        ]);

        $relationship = $this->relationshipRepo->create($request);

        return Response::dataResponse(true, ['relationship' => $relationship]);
    }

    /**
     * Respond to friend request
     * @param Request $request, post request
     *        rules: requires status with value true or false indicating
     *               whether request is accepted or not
     * @param $requester_id, person who friend requested
     * @return JsonResponse
     */
    public function respondToRequest(Request $request, $requester_id)
    {
        $user = $request->get('user');
        $requesteeId = $user->id;

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->hasRelationship($requester_id, $requesteeId)->all()->first();

        if ($relationship == null || $relationship->status == true)
            Exceptions::invalidRequestException(NO_PENDING_REQUEST);

        // status from request indicating accept or ignore
        if ((bool)$request->input('status')) {
            $relationship->update(['status' => true]);
            return Response::dataResponse(true, ['relationship' => $relationship]);
        } else {
            $relationship->delete();
            return Response::successResponse(IGNORED_FRIEND_REQUEST);
        }
    }

    /**
     * Delete a friend
     * @param Request $request, delete request
     * @param $user_id, user to be deleted
     * @return JsonResponse
     */
    public function deleteFriend(Request $request, $user_id)
    {
        $user = $request->get('user');
        $deleterId = $user->id;

        // Cannot delete yourself
        if($deleterId == $user_id)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->hasRelationship($deleterId, $user_id)
            ->hasFriendship()->all()->first();

        if ($relationship == null)
            Exceptions::notFoundException(NOT_FOUND);

        $relationship->delete();

        return Response::successResponse(DELETE_SUCCESS);
    }

    /**
     * Block a user
     * @param Request $request, get request
     * @param $user_id, user to be blocked
     * @return JsonResponse
     */
    public function blockUser(Request $request, $user_id)
    {
        $user = $request->get('user');
        $blockerId = $user->id;

        // Cannot block yourself
        if($blockerId == $user_id)
            Exceptions::invalidRequestException(INVALID_REQUEST_TO_SELF);

        // Query for relationship between these two users to check if it has
        // already existed
        $relationship = $this->relationshipRepo
            ->hasRelationship($blockerId, $user_id)
            ->all()->first();

        if ($relationship) {
            if($relationship->blocked)
                Exceptions::invalidRequestException(EXISTED_RELATIONSHIP);

            $relationship->blocked = true;
            $relationship->requester = $blockerId;
            $relationship->requestee = $user_id;
            $relationship->status = false;
            $relationship->update();
        } else {
            $request->merge([
                'requester' => $blockerId,
                'requestee' => $user_id,
                'blocked' => true
            ]);
            $relationship = $this->relationshipRepo->create($request);
        }
        return Response::dataResponse(true, ['relationship' => $relationship]);
    }

    /**
     * Check if two users are friends
     * @param Request $request
     * @param $user1_id
     * @param $user2_id
     * @return JsonResponse
     */
    public function isFriend(Request $request, $user1_id, $user2_id)
    {
        $relationship = $this->relationshipRepo
            ->hasRelationship($user1_id, $user2_id)
            ->all()->first();

        if($relationship)
            return Response::dataResponse(true, [
                'relationship' => $relationship,
                'is_friend' => $relationship->status
            ]);
        else
            return Response::dataResponse(true, ['is_friend' => false]);
    }

}