<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataValidator;
use Illuminate\Http\Request;
use App\Comment;
use App\User;
use App\EntitysPicture;
use App\Image;
use Illuminate\Support\Facades\Validator;

/**
 * Class CommentController
 * @package App\Http\Controllers
 */
class CommentController extends Controller
{

    /**
     * Creates a comment
     *
     * @param Request $request, post request
     *        rules: requires comment that is not empty and entity_id
     * @return Comment information
     */
    public function create(Request $request)
    {

        $validator = DataValidator::validateComment($request);
        if ($validator->fails())
            return $validator->errors()->all();

        $entity = Entity::create([]);
        $comment = new Comment();

        $comment->entity_id = $entity->id;
        $comment->on_entity_id = $request->input('on_entity_id');
        $comment->comment = $request->input('comment');

        if($request->has('picture')) {
            $image = new Image();
            $entitys_picture = new EntitysPicture();
            ImageController::storeImage($request->file('picture'), $image);
            $image->save();
            $entitys_picture->entity_id = $entity->id;
            $entitys_picture->image_id = $image->id;
            $entitys_picture->save();
        }

        $api_token = $request->header('Authorization');

        $comment->user_id = User::where('api_token', $api_token)->first()->id;
        $comment->save();

        return response(json_encode(['comment' => 'created']), 202);

    }

    /**
     * Returns information on comment
     *
     * @param $comment_id
     * @return Comnent information, json response if comment is not found
     */
    public function read($comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        return $comment;

    }

    /**
     * Edits comment
     *
     * @param Request $request, post request
     *        rules: requires comment that is not empty
     * @param $comment_id
     * @return Comment information, json response if comment is not found
     */
    public function update(Request $request, $comment_id)
    {

        $validator = Validator::make($request->all(), [
            'comment' => 'required|min:1'
        ]);
        if ($validator->fails())
            return $validator->errors()->all();

        $comment = Comment::find($comment_id);
        $api_token = $comment->user->api_token;
        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated']),
                401);
        }

        if ($comment == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        if ($request->has('comment'))
            $comment->comment = $request->input('comment');

        $comment->update();

        return response(json_encode(['comment' => 'updated']), 201);

    }

    /**
     * Deletes a comment, only available if comment belongs to logged in user
     *
     * @param Request $request, delete request
     * @param $comment_id
     * @return json response indicating error if comment is not found, json
     *         respsonse indicating error if user logged in did not make the
     *         comment, or json response indicating comment deleted if it is
     *         successfully deleted
     */
    public function delete(Request $request, $comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        $api_token = $comment->user->api_token;
        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated']),
                401);
        }

        $pictures = $comment->entity->pictures;
        foreach ($pictures as $picture) {
            $picture->image->delete();
        }
        $comment->entity->delete();
        return response(json_encode(['comment' => 'deleted']), 200);

    }

}
