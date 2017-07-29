<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use Illuminate\Container\Container as App;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommentRepository extends Repository
{
    private $imageRepo;
    private $likeRepo;

    public function __construct(App $app, Collection $collection,
                                ImageRepository $imageRepo,
                                LikeRepository $likeRepo)
    {
        parent::__construct($app, $collection);
        $this->imageRepo = $imageRepo;
        $this->likeRepo = $likeRepo;
    }

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