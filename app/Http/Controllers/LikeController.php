<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\LikeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LikeController
 * @package App\Http\Controllers
 */
class LikeController extends Controller
{
    private $likeRepo;

    function __construct(LikeRepository $likeRepo)
    {
        $this->likeRepo = $likeRepo;
    }

    /**
     * Create a Like
     * @param Request $request, post request
     *        rules: requires entity_id
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $user = $request->get('user');
        $likeableId = $request->get('likeable_id');

        $likes = $user->likes;
        foreach($likes as $like)
            if($like->imageable_id == $likeableId)
                Exceptions::invalidRequestException(ALREADY_LIKED_ENTITY);

        $data = [
            'likeable_type' => $this->getLikeableType($request),
            'likeable_id' => $likeableId,
            'user_id' => $user->id
        ];
        $like = $this->likeRepo->create($data);

        return Response::dataResponse(true, ['like' => $like]);
    }

    /**
     * Delete a like
     * @param Request $request , delete request
     * @param $entity_id , entity the like is on
     * @return JsonResponse
     */
    public function delete(Request $request, $like_id)
    {
        $like = $this->likeRepo->read($like_id);
        if (!$like)
            Exceptions::notFoundException(NOT_FOUND);

        /* Validate if user deleting is the same as the user from the token */
        $api_token = $like->user->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $like->delete();
        return Response::successResponse();
    }

    public function getLikeableType(Request $request)
    {
        if($request->is('api/pinpost/like/create'))
            return 'App\Pinpost';

        return null;
    }

}
