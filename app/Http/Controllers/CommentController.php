<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\CommentRepository;
use App\Repositories\ImageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers
 */
class CommentController extends Controller
{
    private $commentRepo;
    private $imageRepo;

    function __construct(CommentRepository $commentRepo,
                        ImageRepository $imageRepo)
    {
        $this->commentRepo = $commentRepo;
        $this->entityRepo = $entityRepo;
        $this->imageRepo = $imageRepo;
    }

    /**
     * Create a comment
     * @param Request $request, post request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $user = $request->get('user');
        $userId = $user->id;
        $commentableId = $request->get('commentable_id');
        $comment = $request->get('comment');

        $data = [
            'user_id' => $userId,
            'commentable_type' => $this->getCommentableType($request),
            'comment' => $comment,
            'commentable_id' => $commentableId
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

        foreach($images as $image) {
            $this->imageRepo->delete($request, $image);
        }
        $this->commentRepo->delete($request, $comment_id);

        return Response::successResponse(DELETE_SUCCESS);
    }

    public function getCommentableType(Request $request)
    {
        if($request->is('api/pinpost/comment/create'))
            return 'App\Pinpost';

        return null;
    }

}
