<?php

namespace App\Repositories;

use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Image;
use Illuminate\Http\Request;

class CommentRepository extends Repository
{
    function model()
    {
        return 'App\Comment';
    }

    public function create(Request $request)
    {
        $entity = $request->get('entity');
        $user = $request->get('user');

        $comment = $this->model->create([
            'entity_id' => $entity->id,
            'on_entity_id' => $request['on_entity_id'],
            'comment' => $request['comment'],
            'user_id' => $user->id
        ]);

        if($comment)
            return $comment;
        else
            Exceptions::unknownErrorException('Could not create comment');
    }

    public function update(Request $request, $id, $attribute = 'id')
    {
        $comment = $this->model->where($attribute, '=', $id)->first();
        if ($comment == null)
            Exceptions::notFoundException('Comment not found');

        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Comment does not associate with this token');

        if ($request->has('comment'))
            $comment->comment = $request['comment'];

        $comment->update();
        return $comment;
    }

}