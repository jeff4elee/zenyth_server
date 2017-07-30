<?php

namespace App\Repositories;

use Illuminate\Container\Container as App;


class CommentRepository extends Repository
{
    function model()
    {
        return 'App\Comment';
    }

}