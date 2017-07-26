<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Relationship;
use Illuminate\Http\Request;
use App\User;

/**
 * Class RelationshipController
 * @package App\Http\Controllers
 */
class RelationshipController extends Controller
{

    /**
     * Send a friend request
     * @param Request $request, post request
     *        rules: requires requestee_id
     * @return response
     */
    public function friendRequest(Request $request)
    {
        $user = $request->get('user');
        $userId = $user->id;
        $requesteeId = $request->input('requestee_id');
        if($userId == $requesteeId)
            Exceptions::invalidRequestException('Cannot send a friend request to yourself');

        /* Verifies if they are already friends or if there is no pending
            request */
        $check = Relationship::where([
            ['requester', '=', $user_id],
            ['requestee', '=', $requesteeId]
        ])->orWhere([
            ['requestee', '=', $user_id],
            ['requester', '=', $requesteeId]
        ])->first();

        if ($check != null)
            Exceptions::invalidRequestException('Existed friendship or pending friend request');

        $relationship = Relationship::create([
            'requester' => $user_id,
            'requestee' => $requesteeId
        ]);

        return Response::dataResponse(true, ['relationship' => $relationship],
            'Successfully created a friend request');

    }

    /**
     * Respond to friend request
     * @param Request $request, post request
     *        rules: requires status with value true or false indicating
     *               whether request is accepted or not
     * @param $requester_id, person who friend requested
     * @return response
     */
    public function respondToRequest(Request $request, $requester_id)
    {

        $user = $request->get('user');
        $requestee_id = $user->id;

        $relationship = Relationship::where([
            ['requester', $requester_id],
            ['requestee', $requestee_id]
        ])->first();

        if ($relationship == null || $relationship->status == true)
            Exceptions::invalidRequestException('No pending request');

        if ($request->input('status')) {
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
     * @return response
     */
    public function deleteFriend(Request $request, $user_id)
    {

        $user = $request->get('user');
        $requester_id = $user->id;

        $relationship = self::friended($requester_id, $user_id);

        if ($relationship == null)
            Exceptions::notFoundException('Relationship not found');

        $relationship->delete();

        return Response::successResponse('Unfriended');

    }

    /**
     * Block a user
     * @param Request $request, get request
     * @param $user_id, user to be blocked
     * @return response
     */
    public function blockUser(Request $request, $user_id)
    {

        $user = $request->get('user');
        $requester_id = $user->id;

        $relationship = self::friended($requester_id, $user_id);

        if ($relationship != null) {
            $relationship->blocked = true;
            $relationship->requester = $requester_id;
            $relationship->requestee = $user_id;
            $relationship->status = false;
            $relationship->update();
        } else {
            $relationship = Relationship::create([
                'requester' => $requester_id,
                'requestee' => $user_id,
                'blocked' => true
            ]);
        }
        return Response::dataResponse(true, ['relationship' => $relationship],
            'Successfully blocked user');
    }

    /**
     * Helper function to check if two users are friends
     * @param $user1_id
     * @param $user2_id
     * @return mixed, relationship information if two users are friends, else
     *                null
     */
    static public function friended($user1_id, $user2_id)
    {

        $relationship = Relationship::where([
            ['requester', '=', $user1_id],
            ['requestee', '=', $user2_id],
            ['status', '=', true]
        ])->orWhere([
            ['requester', '=', $user2_id],
            ['requestee', '=', $user1_id],
            ['status', '=', true]
        ])->first();

        return $relationship;

    }

    /**
     * Check if two users are friends
     * @param Request $request
     * @param $user1_id
     * @param $user2_id
     * @return response
     */
    public function isFriend(Request $request, $user1_id, $user2_id)
    {
        if(User::find($user1_id) == null)
            Exceptions::invalidRequestException('Either user does not exist');
        else if(User::find($user2_id) == null)
            Exceptions::invalidRequestException('Either user does not exist');

        if($relationship = self::friended($user1_id, $user2_id)) {
            return Response::dataResponse(true, [
                'relationship' => $relationship,
                'is_friend' => true
            ]);
        } else {
            return Response::dataResponse(true, ['is_friend' => false]);
        }
    }

}