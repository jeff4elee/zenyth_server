<?php


namespace App\Repositories\Criteria\Relationship;

use App\Repositories\Criteria\Criteria;

class AllFriends extends Criteria
{
    protected $userId;

    function __construct($userId)
    {
        $this->userId = $userId;
    }

    public  function apply($model)
    {
        $queryOne = $model->select('users.*')
            ->join('users', 'users.id', '=', 'requester')
            ->where('requestee', '=', $this->userId)
            ->where('status', '=', true);

        $queryTwo = $model->select('users.*')
            ->join('users', 'users.id', '=', 'requestee')
            ->where('requester', '=', $this->userId)
            ->where('status', '=', true);;

        $query = $queryOne->union($queryTwo);
        return $query;
    }
}