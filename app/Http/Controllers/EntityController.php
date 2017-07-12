<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Response;
use App\Entity;

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
     * @return likes count
     */
    public function likesCount($entity_id)
    {

        $count = Entity::find($entity_id)->likesCount();
        return response(json_encode([
            'success' => true,
            'data' => $count
        ]), 200);

    }

    /**
     * Returns number of comments of an entity
     *
     * @param $entity_id
     * @return comments count
     */
    public function commentsCount($entity_id)
    {

        $count = Entity::find($entity_id)->commentsCount();
        return response(json_encode([
            'success' => true,
            'data' => $count
        ]), 200);

    }

    /**
     * Returns users who liked the entity
     *
     * @param $entity_id
     * @return array of users
     */
    public function likesUsers($entity_id)
    {

        $entity = Entity::find($entity_id);

        $users_arr = [];
        $likes = $entity->likes;

        foreach ($likes as $like) {
            array_push($users_arr, $like->user);
        }

        return response(json_encode([
            'success' => true,
            'data' => $users_arr
        ]), 200);

    }


}