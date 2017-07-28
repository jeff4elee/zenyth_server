<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use Illuminate\Http\Request;

class CommentRepository extends Repository
{
    function model()
    {
        return 'App\Comment';
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
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
            Exceptions::unknownErrorException(OBJECT_FAIL_TO_CREATE);
    }

    /**
     * @param Request $request
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(Request $request, $id, $attribute = 'id')
    {
        $comment = $this->model->where($attribute, '=', $id)->first();
        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        if ($request->has('comment'))
            $comment->comment = $request['comment'];

        $comment->update();
        return $comment;
    }

}