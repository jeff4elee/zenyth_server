<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function create(Request $request)
    {

        $comment = new Comment();

        $comment->entity_id = $request->get('entity_id');
        $comment->comment = $request->get('comment');
        $comment->user_id = $request->get('user_id');

        $comment->save();

        return 1;

    }

    public function read($comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null) {
            return 0;
        }

        return $comment;

    }

    public function update(Request $request, $comment_id)
    {

        $comment = Comment::find($comment_id);

        if ($comment == null) {
            return 0;
        }

        if ($request->has('comment'))
            $comment->title = $request->get('comment');

        $comment->update();

        return 1;

    }

    public function delete($comment_id)
    {

        $comment = Like::find($comment_id);

        if ($comment == null) {
            return 0;
        }

        $comment->delete();

        return 1;

    }


    public function count($entity_id)
    {

        $comments = Comment::where('entity_id', '=', $entity_id);

        if ($comments == null) {
            return 0;
        }

        return $comments->count();

    }


}
