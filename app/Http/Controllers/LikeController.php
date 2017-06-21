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

    public function count($like_id)
    {

        $entities = Like::find($like_id);

        if ($entities == null) {
            return 0;
        }

        return $entities->count();

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
