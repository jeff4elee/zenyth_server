<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\CommentRepository;
use App\Repositories\ImageRepository;
use App\Repositories\PinpostRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers
 */
class CommentController extends Controller
{
    private $pinpostRepo;
    private $commentRepo;
    private $imageRepo;

    function __construct(PinpostRepository $pinpostRepo,
                         CommentRepository $commentRepo,
                         ImageRepository $imageRepo)
    {
        $this->pinpostRepo = $pinpostRepo;
        $this->commentRepo = $commentRepo;
        $this->imageRepo = $imageRepo;
    }

    /**
     * Create a comment
     * @param Request $request, post request
     * @return JsonResponse
     */
    public function create(Request $request, $commentable_id)
    {
        $user = $request->get('user');
        $userId = $user->id;
        $comment = $request->get('comment');
        $commentableType = $this->getCommentableType($request);

        $exist = $this->commentableExists($commentableType, $commentable_id);
        if(!$exist)
            Exceptions::notFoundException(NOT_FOUND);

        $data = [
            'user_id' => $userId,
            'commentable_type' => $commentableType,
            'comment' => $comment,
            'commentable_id' => $commentable_id
        ];

        $comment = $this->commentRepo->create($data);

        return Response::dataResponse(true, ['comment' => $comment]);

    }

    /**
     * Return information on comment
     * @param Request $request
     * @param $comment_id
     * @return JsonResponse
     */
    public function read(Request $request, $comment_id)
    {
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
            $comment = $this->commentRepo->read($comment_id, $fields);
        }
        else
            $comment = $this->commentRepo->read($comment_id);

        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        return Response::dataResponse(true, ['comment' => $comment]);

    }

    /**
     * Edit comment
     * @param Request $request, post request
     *        rules: requires comment that is not empty
     * @param $comment_id
     * @return JsonResponse
     */
    public function update(Request $request, $comment_id)
    {
        $comment = $this->commentRepo->update($request, $comment_id);

        return Response::dataResponse(true, ['comment' => $comment]);
    }

    /**
     * Delete a comment, only available if comment belongs to logged in user
     * @param Request $request, delete request
     * @param $comment_id
     * @return JsonResponse
     */
    public function delete(Request $request, $comment_id)
    {
        $comment = $this->commentRepo->read($comment_id);
        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        /* Validate if user deleting is the same as the user from the token */
        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $images = $comment->images;
        foreach($images as $image)
            $this->imageRepo->remove($image);

        $likes = $comment->likes;
        foreach($likes as $like)
            $like->delete();

        $this->commentRepo->delete($request, $comment_id);

        return Response::successResponse(DELETE_SUCCESS);
    }

    public function getCommentableType(Request $request)
    {
        if($request->is('api/pinpost/comment/create/*'))
            return 'App\Pinpost';

        return null;
    }

    public function commentableExists($commentableType, $commentableId)
    {
        if($commentableType == 'App\Pinpost') {
            if($this->pinpostRepo->findBy('id', $commentableId))
                return true;
        }
        return false;
    }

}
