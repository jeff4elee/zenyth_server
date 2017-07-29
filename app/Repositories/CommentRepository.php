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

    /**
     * Delete an object given the id
     * @param $request
     * @param $id
     * @return bool|null
     */
    public function delete($request, $id)
    {
        $comment = $this->model->find($id);
        $images = $comment->images;
        foreach($images as $image)
            $this->imageRepo->remove($image);

        $likes = $comment->likes;
        foreach($likes as $like)
            $this->likeRepo->remove($like);

        return $comment->delete();
    }

    /**
     * Delete an object if the object is already provided
     * @param $comment
     * @return mixed
     */
    public function remove($comment)
    {
        $images = $comment->images;
        foreach($images as $image)
            $this->imageRepo->remove($image);

        $likes = $comment->likes;
        foreach($likes as $like)
            $this->likeRepo->remove($like);

        return $comment->delete();
    }

}