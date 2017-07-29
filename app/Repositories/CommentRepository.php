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
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update($request, $id, $attribute = 'id')
    {
        $comment = $this->model->where($attribute, '=', $id)->first();
        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $comment->comment = $request['comment'];

        $comment->update();
        return $comment;
    }

}