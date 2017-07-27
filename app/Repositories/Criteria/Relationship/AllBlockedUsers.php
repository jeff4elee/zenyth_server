<?php


namespace App\Repositories\Criteria\Relationship;

use App\Repositories\Criteria\Criteria;

class AllBlockedUsers extends Criteria
{
    protected $userId;

    function __construct($userId)
    {
        $this->userId = $userId;
    }


    public  function apply($model)
    {
        $query = $model->select('users.*')
            ->join('users', 'users.id', '=', 'requestee')
            ->where('requester', '=', $this->userId)
            ->where('blocked', '=', true);

        return $query;
    }
}