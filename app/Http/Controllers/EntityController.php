<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Response;
use App\Entity;

class EntityController extends Controller
{

    public function likesCount($entity_id)
    {

        return Entity::find($entity_id)->likesCount();

    }

    public function commentsCount($entity_id)
    {

        return Entity::find($entity_id)->commentsCount();

    }

    public function likesUsers($entity_id)
    {

        $entity = Entity::find($entity_id);

        $users_arr = [];
        $likes = $entity->likes;

        foreach($likes as $like) {
            array_push($users_arr, $like->user);
        }

        return $users_arr;

    }

    public function comments($entity_id) {

        $entity = Entity::find($entity_id);

        $comments_arr = [];
        $comments = $entity->comments;

        foreach($comments as $comment) {
            array_push($comments_arr, $comment);
        }

        return $comments_arr;

    }

}