<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\Criteria\Relationship\HaveRelationship;
use App\Repositories\Criteria\Relationship\IsFriend;
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
            Exceptions::invalidRequestException('Cannot send a friend request to yourself');

        $this->relationshipRepo->pushCriteria(new HaveRelationship($userId,
            $requesteeId));
        $relationship = $this->relationshipRepo->all();

        if (count($relationship) != 0)
            Exceptions::invalidRequestException('Existed friendship or pending friend request');

        $request->merge([
            'requester' => $userId,
            'requestee' => $requesteeId
        ]);

        $this->relationshipRepo->resetScope();
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

        $this->relationshipRepo->pushCriteria(
            new HaveRelationship($requester_id, $requesteeId));

        $relationship = $this->relationshipRepo->all();
        $relationship = $relationship->first();

        if ($relationship == null || $relationship->status == true)
            Exceptions::invalidRequestException('No pending request');

        if ((bool)$request->input('status')) {
            $relationship->update(['status' => true]);
            return Response::dataResponse(true, ['relationship' => $relationship],
                'Friendship created');
        } else {
            $relationship->delete();
            return Response::successResponse('Friend request ignored');
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

        if($deleterId == $user_id)
            Exceptions::invalidRequestException('Cannot delete yourself');

        $this->relationshipRepo->pushCriteria(
            new HaveRelationship($deleterId, $user_id));

        $relationship = $this->relationshipRepo->all();
        $relationship = $relationship->first();

        if ($relationship == null)
            Exceptions::notFoundException(NOT_FOUND);

        $relationship->delete();

        return Response::successResponse('Unfriended');
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

        if($blockerId == $user_id)
            Exceptions::invalidRequestException('Cannot block yourself');

        $this->relationshipRepo->pushCriteria(new HaveRelationship(
            $blockerId, $user_id
        ));
        $this->relationshipRepo->pushCriteria(new IsFriend());
        $relationship = $this->relationshipRepo->all();
        $relationship = $relationship->first();

        if ($relationship) {
            $relationship->blocked = true;
            $relationship->requester = $blockerId;
            $relationship->requestee = $user_id;
            $relationship->status = false;
            $relationship->update();
        } else {
            $this->relationshipRepo->resetScope();
            $request->merge([
                'requester' => $blockerId,
                'requestee' => $user_id,
                'blocked' => true
            ]);
            $relationship = $this->relationshipRepo->create($request);
        }
        return Response::dataResponse(true, ['relationship' => $relationship],
            'Successfully blocked user');
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
        $this->relationshipRepo->pushCriteria(new HaveRelationship(
            $user1_id, $user2_id
        ));
        $this->relationshipRepo->pushCriteria(new IsFriend());
        $relationship = $this->relationshipRepo->all();
        $relationship = $relationship->first();

        if($relationship) {
            return Response::dataResponse(true, [
                'relationship' => $relationship,
                'is_friend' => true
            ]);
        } else {
            return Response::dataResponse(true, ['is_friend' => false]);
        }
    }

}