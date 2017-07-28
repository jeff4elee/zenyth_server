<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\RelationshipRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $relationship = $this->relationshipRepo
            ->hasRelationship($userId, $requesteeId)->all()->first();

        if ($relationship)
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

        $relationship = $this->relationshipRepo
            ->hasRelationship($requester_id, $requesteeId)->all()->first();

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

        $relationship = $this->relationshipRepo
            ->hasRelationship($deleterId, $user_id)
            ->hasFriendship()->all()->first();

        if ($relationship == null)
            Exceptions::notFoundException(NOT_FOUND);

        $relationship->delete();

        $this->swapVal($user->id, $deleterId);

        $key = 'friend' . $user->id . $deleterId;

        if(Cache::has($key)){
            return Cache::forget($key);
        }

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

        $relationship = $this->relationshipRepo
            ->hasRelationship($blockerId, $user_id)
            ->hasFriendship()->all()->first();

        if ($relationship) {
            if($relationship->blocked)
                Exceptions::invalidRequestException('Already blocked');

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


        $this->swapVal($user->id, $blockerId);

        $key = 'friend' . $user->id . $blockerId;

        if(Cache::has($key)){
            return Cache::forget($key);
        }

        return Response::dataResponse(true, ['relationship' => $relationship],
            'Successfully blocked user');
    }

    private function swapVal($user1_id, $user2_id){
        if(intval($user1_id) > intval($user2_id)){
            $tmp=$user1_id;
            $user1_id = $user2_id;
            $user2_id = $tmp;
        }
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

        $this->swapVal($user1_id, $user2_id);

        $key = 'friend' . $user1_id . $user2_id;

        if(Cache::has($key)){
            return Cache::get($key);
        }

        $relationship = $this->relationshipRepo
            ->hasRelationship($user1_id, $user2_id)
            ->hasFriendship()->all()->first();

        if($relationship) {
            $response = Response::dataResponse(true, [
                'relationship' => $relationship,
                'is_friend' => true
            ]);

        } else {
            $response = Response::dataResponse(true, ['is_friend' => false]);
        }

        Cache::put($key, $response, 20);
        return $response;

    }

}