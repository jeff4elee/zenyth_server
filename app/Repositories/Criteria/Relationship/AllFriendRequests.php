<?php


namespace App\Repositories\Criteria\Relationship;

use App\Repositories\Criteria\Criteria;

class AllFriendRequests extends Criteria
{
    protected $userId;

    function __construct($userId)
    {
        $this->userId = $userId;
    }

    public  function apply($model)
    {
        $query = $model->select('users.*')
            ->join('users', 'users.id', '=', 'requester')
            ->where('requestee', '=', $this->userId)
            ->where('status', '=', false);

        return $query;
    }
}