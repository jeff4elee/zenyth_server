<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataValidator;
use Illuminate\Http\Request;
use App\Like;
use App\User;

/**
 * Class LikeController
 * @package App\Http\Controllers
 */
class LikeController extends Controller
{

    /**
     * Creates a Like
     *
     * @param Request $request, post request
     *        rules: requires entity_id
     * @return Like information
     */
    public function create(Request $request)
    {
        $validator = DataValidator::validateLike($request);
        if($validator->fails())
            return $validator->errors()->all();

        $like = new Like();

        $like->entity_id = $request->input('entity_id');

        $api_token = $request->header('Authorization');
        $user_id = User::where('api_token', $api_token)->first()->id;
        $like->user_id = $user_id;

        $like->save();

        return $like;

    }

    /**
     * Deletes a like
     *
     * @param $like_id, like to be deleted
     * @return json response if like is not found or if like is successfully
     *         deleted
     */
    public function delete($like_id)
    {

        $like = Like::find($like_id);

        if ($like == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        $like->delete();

        return response(json_encode(['like status' => 'deleted']), 202);

    }

}
