<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Image;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers
 */
class CommentController extends Controller
{

    /**
     * Create a comment
     * @param Request $request, post request
     *        rules: requires comment that is not empty and entity_id
     * @return response
     */
    public function create(Request $request)
    {
        $entity = Entity::create([]);
        $comment = new Comment();

        $comment->entity_id = $entity->id;
        $comment->on_entity_id = $request->input('on_entity_id');
        $comment->comment = $request->input('comment');

        if($request->has('picture')) {
            $image = new Image();
            ImageController::storeImage($request->file('picture'), $image);
            EntitysPicture::create([
                'entity_id' => $entity->id,
                'image_id' => $image->id
            ]);
        }

        $user = $request->get('user');

        $comment->user_id = $user->id;
        $comment->save();

        return Response::dataResponse(true, ['comment' => $comment],
            'Successfully created comment');

    }

    /**
     * Return information on comment
     * @param $comment_id
     * @return response
     */
    public function read($comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null)
            Exceptions::notFoundException('Comment not found');

        return Response::dataResponse(true, ['comment' => $comment]);

    }

    /**
     * Edit comment
     * @param Request $request, post request
     *        rules: requires comment that is not empty
     * @param $comment_id
     * @return response
     */
    public function update(Request $request, $comment_id)
    {

        $comment = Comment::find($comment_id);
        if ($comment == null)
            Exceptions::notFoundException('Comment not found');

        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Comment does not associate with this token');

        if ($comment == null)
            Exceptions::notFoundException('Comment not found');

        if ($request->has('comment'))
            $comment->comment = $request->input('comment');

        $comment->update();

        return Response::dataResponse(true, ['comment' => $comment],
            'successfully updated comment');

    }

    /**
     * Delete a comment, only available if comment belongs to logged in user
     * @param Request $request, delete request
     * @param $comment_id
     * @return response
     */
    public function delete(Request $request, $comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null)
            Exceptions::notFoundException('Comment not found');

        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Comment does not associate with this token');

        $pictures = $comment->entity->pictures;
        foreach ($pictures as $picture) {
            $picture->image->delete();
        }
        $comment->entity->delete();
        return Response::successResponse('successfully deleted comment');

    }

}
