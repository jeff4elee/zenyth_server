<?php

namespace App\Http\Controllers;


use App\Entity;
use App\Exceptions\ResponseHandler as Response;

/**
 * Class EntityController
 * @package App\Http\Controllers
 */
class EntityController extends Controller
{

    /**
     * Returns number of likes of an entity
     *
     * @param $entity_id
     * @return response
     */
    public function likesCount($entity_id)
    {

        $count = Entity::find($entity_id)->likesCount();
        return Response::dataResponse(true, ['count' => $count]);

    }

    /**
     * Returns number of comments of an entity
     *
     * @param $entity_id
     * @return response
     */
    public function commentsCount($entity_id)
    {

        $count = Entity::find($entity_id)->commentsCount();
        return Response::dataResponse(true, ['count' => $count]);

    }

    /**
     * Returns users who liked the entity
     *
     * @param $entity_id
     * @return response
     */
    public function likesUsers($entity_id)
    {

        $entity = Entity::find($entity_id);

        $users_arr = [];
        $likes = $entity->likes;

        foreach ($likes as $like) {
            array_push($users_arr, $like->user);
        }

        return Response::dataResponse(true, ['users' => $users_arr]);

    }


}