<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
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
            return Response::validatorErrorResponse($validator);

        $like = new Like();

        $like->entity_id = $request->input('entity_id');

        $user = $request->get('user');
        $user_id = $user->id;
        $like->user_id = $user_id;

        $like->save();

        return Response::dataResponse(true, ['like' => $like]);
    }

    /**
     * Deletes a like
     *
     * @param Request $request , delete request
     * @param $entity_id , entity the like is on
     * @return json response if like is not found or if like is successfully
     *         deleted
     */
    public function delete(Request $request, $entity_id)
    {

        $user = $request->get('user');
        $user_id = $user->id;
        $like = Like::where([
            ['entity_id', '=', $entity_id],
            ['user_id', '=', $user_id]
        ]);

        if ($like == null) {
            return Response::errorResponse(Exceptions::notFoundException());
        }

        $like->delete();

        return Response::successResponse();

    }

}
