<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Response;
use App\Entity;

class EntityController extends Controller
{

    public function likesCount($entity_id)
    {

        return Entity::find($entity_id)->likesCount;

    }

    public function commentsCount($entity_id)
    {

        return Entity::find($entity_id)->commentsCount;

    }

}