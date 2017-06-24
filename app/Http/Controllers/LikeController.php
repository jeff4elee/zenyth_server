<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Like;
use App\User;

class LikeController extends Controller
{

    public function create(Request $request)
    {
        $this->validate($request, [
            'entity_id' => 'required'
        ]);

        $like = new Like();

        $like->entity_id = $request->input('entity_id');

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;
        $like->user_id = $user_id;

        $like->save();

        return $like;

    }

    public function delete($like_id)
    {

        $like = Like::find($like_id);

        if ($like == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        $like->delete();

        return response(json_encode(['like status' => 'deleted']), 200);

    }

}
