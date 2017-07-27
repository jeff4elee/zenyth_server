<?php


namespace App\Repositories;


class EntityRepository extends Repository
{
    function model()
    {
        return 'App\Entity';
    }
}