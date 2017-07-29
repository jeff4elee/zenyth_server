<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\CommentRepository;
use App\Repositories\LikeRepository;
use App\Repositories\PinpostRepository;
use App\Repositories\ReplyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LikeController
 * @package App\Http\Controllers
 */
class LikeController extends Controller
{
    private $likeRepo;
    private $commentRepo;
    private $pinpostRepo;
    private $replyRepo;

    function __construct(LikeRepository $likeRepo,
                        PinpostRepository $pinpostRepo,
                        CommentRepository $commentRepo,
                        ReplyRepository $replyRepo)
    {
        $this->likeRepo = $likeRepo;
        $this->commentRepo = $commentRepo;
        $this->pinpostRepo = $pinpostRepo;
        $this->replyRepo = $replyRepo;
    }

    /**
     * Create a Like
     * @param Request $request, post request
     *        rules: requires entity_id
     * @return JsonResponse
     */
    public function create(Request $request, $likeable_id)
    {
        $user = $request->get('user');
        $likeableType = $this->getLikeableType($request);

        // Check if likeable object exists
        $this->likeableExists($likeableType, $likeable_id);

        // Go through to see if this user has already liked this likeable
        // object
        $likes = $user->likes;
        foreach($likes as $like)
            if($like->likeable_id == $likeable_id
                && $like->likeable_type == $likeableType)
                Exceptions::invalidRequestException(ALREADY_LIKED_ENTITY);

        $data = [
            'likeable_type' => $likeableType,
            'likeable_id' => $likeable_id,
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

        // Validate if user deleting is the same as the user from the token
        $api_token = $like->user->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $like->delete();
        return Response::successResponse(DELETE_SUCCESS);
    }

    /**
     * Get likeable type
     * @param Request $request
     * @return null|string
     */
    public function getLikeableType(Request $request)
    {
        if($request->is('api/pinpost/like/create/*'))
            return 'App\Pinpost';
        else if($request->is('api/comment/like/create/*'))
            return 'App\Comment';
        else if($request->is('api/reply/like/create/*'))
            return 'App\Reply';

        Exceptions::invalidRequestException();
    }

    /**
     * Check if likeable object exists
     * @param $likeableType
     * @param $likeableId
     * @return bool
     */
    public function likeableExists($likeableType, $likeableId)
    {
        if($likeableType == 'App\Pinpost')
            if($this->pinpostRepo->findBy('id', $likeableId))
                return true;

        else if($likeableType == 'App\Comment')
            if($this->commentRepo->findBy('id', $likeableId))
                return true;

        else if($likeableType == 'App\Reply')
            if($this->replyRepo->findBy('id', $likeableId))
                return true;

        Exceptions::notFoundException(NOT_FOUND);
    }

}
