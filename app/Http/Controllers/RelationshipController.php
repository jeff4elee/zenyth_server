<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
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

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $validator = Validator::make($request->all(), [
            'requestee_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

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
            return response(json_encode(['error' => 'friends or pending 
                            request']), 400);

        $relationship = Relationship::create([
            'requester' => $user_id,
            'requestee' => $request->input('requestee_id')
        ]);

        return $relationship;

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

        $api_token = $request->header('Authorization');
        $requestee_id = User::where('api_token', $api_token)->first()->id;

        $relationship = Relationship::where([
            ['requester', $requester_id],
            ['requestee', $requestee_id]
        ])->first();

        if ($relationship == null || $relationship->status == true)
            return response(json_encode(['error' => 'No pending request']),
                404);

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        if ($request->input('status')) {
            $relationship->update(['status' => true]);
            return $relationship;
        } else {
            $relationship->delete();
            return response(json_encode(['relationship' => 'deleted']), 200);
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

        $api_token = $request->header('Authorization');
        $requester_id = User::where('api_token', $api_token)->first()->id;

        $relationship = self::friended($requester_id, $user_id);

        if ($relationship == null)
            return response(json_encode(['relationship' => 'not friends']),
                404);

        $relationship->delete();
        return response(json_encode(['relationship' => 'unfriended']), 200);

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

        $api_token = $request->header('Authorization');
        $requester_id = User::where('api_token', $api_token)->first()->id;

        $relationship = self::friended($requester_id, $user_id);
        if ($relationship != null) {
            $relationship->blocked = true;
            $relationship->requester = $requester_id;
            $relationship->requestee = $user_id;
            $relationship->status = false;
            $relationship->update();
        } else {
            Relationship::create([
                'requester' => $requester_id,
                'requestee' => $user_id,
                'blocked' => true
            ]);
        }
        return response(json_encode(['blocked' => true]), 200);
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