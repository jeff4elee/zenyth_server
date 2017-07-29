<?php


namespace App\Repositories;

class TaggableRepository extends Repository
{
    function model()
    {
        return 'App\Taggable';
    }
}