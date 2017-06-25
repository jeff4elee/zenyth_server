<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use Illuminate\Support\Facades\Validator;
use App\Relationship;
use App\User;

class RelationshipController extends Controller
{

    public function friendRequest(Request $request)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $validator = Validator::make($request->all(), [
            'requestee_id' => 'required|exists:users,id'
        ]);

        if($validator->fails()) {
            return $validator->errors()->all();
        }

        /* Verifies if they are already friends */
        if($this->friended($user_id, $request->input('requestee_id')) != null)
            return response(json_encode(['relationship' => friends]), 200);

        $relationship = Relationship::create([
            'requester_id' => $user_id,
            'requestee_id' => $request->input('requestee_id')
        ]);

        return $relationship;

    }

    public function respondToRequest(Request $request, $relationship_id)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;

        $relationship = Relationship::find($relationship_id);
        $requestee_id = User::find($relationship->requestee)->id;
        if($user_id != $requestee_id)
            return response(json_encode(['error' => 'Unauthenticated']), 401);

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if($validator->fails()) {
            return $validator->errors()->all();
        }

        if($request->input('status')) {
            $relationship->status = true;
            return $relationship;
        } else {
            $relationship->delete();
            return response(json_encode(['relationship' => 'denied']), 200);
        }

    }

    public function deleteFriend(Request $request, $relationship_id)
    {

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;
        $relationship = Relationship::find($relationship_id);
        if($user_id != $relationship->requester ||
            $user_id != $relationship->requestee) {
            return response(json_encode(['error' => 'Unauthenticated']), 401);
        }

        $relationship->delete();
        return response(json_encode(['relationship' => 'unfriended']), 200);

    }

    protected function friended($user1_id, $user2_id)
    {

        $relationship1 = Relationship::where('requester', $user1_id)
                        ->andWhere('requestee', $user2_id)->first();
        if($relationship1 != null) {
            return $relationship1;
        }

        $relationship2 = Relationship::where('requester', $user2_id)
                        ->andWhere('requestee', $user1_id)->first();
        if($relationship2 != null) {
            return $relationship2;
        }
        return null;

    }

}