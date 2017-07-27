<?php

namespace App\Http\Repositories;


class UserRepository extends Repository
{
    function model()
    {
        return 'App\User';
    }
}