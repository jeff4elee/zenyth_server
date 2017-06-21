<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Like;

class LikeController extends Controller
{

    public function create(Request $request)
    {

        $like = new Like();

        $like->entity_id = $request->get('entity_id');
        $like->user_id = $request->get('user_id');

        $like->save();

        return 1;

    }

    public function count($entity_id)
    {

        return Entity::likesCount();

    }

    public function delete($like_id)
    {

        $like = Like::find($like_id);

        if ($like == null) {
            return 0;
        }

        $like->delete();

        return 1;

    }

}
