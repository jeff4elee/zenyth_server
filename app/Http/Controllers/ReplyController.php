<?php


namespace App\Http\Controllers;


use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\CommentRepository;
use App\Repositories\ReplyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    private $replyRepo;
    private $commentRepo;

    public function __construct(ReplyRepository $replyRepo,
                                CommentRepository $commentRepo)
    {
        $this->replyRepo = $replyRepo;
        $this->commentRepo = $commentRepo;
    }

    /**
     * Create a reply
     * @param Request $request, post request
     * @param @comment_id
     * @return JsonResponse
     */
    public function create(Request $request, $comment_id)
    {
        $user = $request->get('user');
        $text = $request['text'];

        // Check if the comment exists
        if(!$this->commentRepo->findBy('id', $comment_id))
            Exceptions::notFoundException(NOT_FOUND);

        // Data of reply
        $data = [
            'text' => $text,
            'user_id' => $user->id,
            'comment_id' => $comment_id
        ];
        $reply = $this->replyRepo->create($data);
        return Response::dataResponse(true, ['reply' => $reply]);
    }

    /**
     * Return information on reply
     * @param Request $request
     * @param $reply_id
     * @return JsonResponse
     */
    public function read(Request $request, $reply_id)
    {
        if($request->has('fields')) {
            // Specifies fields to return
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
            $reply = $this->replyRepo->read($reply_id, $fields);
        }
        else
            $reply = $this->replyRepo->read($reply_id);

        if ($reply == null)
            Exceptions::notFoundException(NOT_FOUND);

        return Response::dataResponse(true, ['reply' => $reply]);
    }

    /**
     * Get all image objects of this reply
     * @param Request $request
     * @param $reply_id
     * @return JsonResponse
     */
    public function readImages(Request $request, $reply_id)
    {
        $reply = $this->replyRepo->read($reply_id);
        $images = $reply->images;

        return Response::dataResponse(true, [
            'reply' => [
                'images' => $images
            ]
        ]);
    }

    /**
     * Edit reply
     * @param Request $request, post request
     *        rules: requires reply that is not empty
     * @param $reply_id
     * @return JsonResponse
     */
    public function update(Request $request, $reply_id)
    {
        $reply = $this->replyRepo->read($reply_id);

        if ($reply == null)
            Exceptions::notFoundException(NOT_FOUND);

        // Validate if reply belongs to user
        $api_token = $reply->creator->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $request->except(['user_id']);
        $this->replyRepo->update($request, $reply);

        return Response::dataResponse(true, ['reply' => $reply]);
    }

    /**
     * Delete a reply, only available if reply belongs to logged in user
     * @param Request $request, delete request
     * @param $reply_id
     * @return JsonResponse
     */
    public function delete(Request $request, $reply_id)
    {
        $reply = $this->replyRepo->read($reply_id);
        if ($reply == null)
            Exceptions::notFoundException(NOT_FOUND);

        // Validate if reply belongs to user
        $api_token = $reply->creator->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $this->replyRepo->delete($reply);

        return Response::successResponse(DELETE_SUCCESS);
    }

    /**
     * Fetch all likes of this reply
     * @param Request $request
     * @param $reply_id
     * @return JsonResponse
     */
    public function fetchLikes(Request $request, $reply_id)
    {
        $reply = $this->replyRepo->read($reply_id);
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
        } else
            $fields = ['*'];

        return Response::dataResponse(true, [
            'likes' => $reply->likes()->get($fields)
        ]);
    }

    /**
     * Get the number of likes of this reply
     * @param Request $request
     * @param $reply_id
     * @return JsonResponse
     */
    public function likesCount(Request $request, $reply_id)
    {
        $reply = $this->replyRepo->read($reply_id);
        return Response::dataResponse(true, [
            'count' => $reply->likesCount()
        ]);
    }
}