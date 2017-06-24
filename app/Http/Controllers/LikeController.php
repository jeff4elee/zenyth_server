<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Like;

class LikeController extends Controller
{

    public function create(Request $request)
    {

        $like = new Like();

        $like->entity_id = $request->input('entity_id');
        $like->user_id = $request->input('user_id');

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
