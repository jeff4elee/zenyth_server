<?php


namespace App\Repositories;


use App\Exceptions\Exceptions;

class ReplyRepository extends Repository
{
    function model()
    {
        return 'App\Reply';
    }

    public function update($request, $id, $attribute = 'id')
    {
        $reply = $this->model->where($attribute, '=', $id)->first();
        if ($reply == null)
            Exceptions::notFoundException(NOT_FOUND);

        $api_token = $reply->user->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $reply->text = $request['text'];

        $reply->update();
        return $reply;
    }
}