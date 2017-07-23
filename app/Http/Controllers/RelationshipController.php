<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
use App\Http\Requests\DataValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Relationship;
use App\User;

/**
 * Class RelationshipController
 * @package App\Http\Controllers
 */
class RelationshipController extends Controller
{

    /**
     * Sends a friend request
     *
     * @param Request $request, post request
     *        rules: requires requestee_id
     * @return relationship information or json response if already friends or
     * if there is a pending friend request
     */
    public function friendRequest(Request $request)
    {

        $validator = DataValidator::validateFriendRequest($request);
        if ($validator->fails())
            return Response::validatorErrorResponse($validator);

        $user = $request->get('user');
        $user_id = $user->id;

        /* Verifies if they are already friends or if there is no pending
            request */
        $check = Relationship::where([
            ['requester', '=', $user_id],
            ['requestee', '=', $request->input('requestee_id')]
        ])->orWhere([
            ['requestee', '=', $user_id],
            ['requester', '=', $request->input('requestee_id')]
        ])->first();

        if ($check != null)
            return Response::errorResponse(Exceptions::invalidRequestException(),
                'friends or pending request');

        $relationship = Relationship::create([
            'requester' => $user_id,
            'requestee' => $request->input('requestee_id')
        ]);

        return Response::dataResponse(true, ['relationship' => $relationship],
            'successfully created a friend request');

    }

    /**
     * Responds to friend request
     *
     * @param Request $request, post request
     *        rules: requires status with value true or false indicating
     *               whether request is accepted or not
     * @param $requester_id, person who friend requested
     * @return relationship information if friend request is accepted, json
     *         response if there is no pending request or json response
     *         indicating that the relationship was deleted
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
            return Response::errorResponse(Exceptions::invalidRequestException(),
                'No pending request');

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails())
            return Response::validatorErrorResponse($validator);

        if ($request->input('status')) {
            $relationship->update(['status' => true]);
            return Response::dataResponse(true, ['relationship' => $relationship],
                'friendship created');
        } else {
            $relationship->delete();
            return Response::successResponse('friend request ignored');
        }

    }

    /**
     * Deletes a friend
     *
     * @param Request $request, delete request
     * @param $user_id, user to be deleted
     * @return json response if they are not friends, or json response
     *         indicating the relationship was deleted
     */
    public function deleteFriend(Request $request, $user_id)
    {

        $user = $request->get('user');
        $requester_id = $user->id;

        $relationship = self::friended($requester_id, $user_id);

        if ($relationship == null)
            return response(json_encode([

                'success' => false,
                'errors' => ['not friends']

            ]), 200);

        $relationship->delete();
        return response(json_encode([

            'success' => true,
            'data' => [
                'relationship' => 'unfriended'
            ]

        ]), 200);

    }

    /**
     * Blocks a user
     *
     * @param Request $request, get request
     * @param $user_id, user to be blocked
     * @return json response indicating that user is blocked
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
            'successfully blocked user');
    }

    /**
     * Checks if two users are friends
     *
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

}