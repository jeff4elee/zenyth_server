<?php

namespace App\Repositories\Criteria\Pinpost;

use App\Repositories\Criteria\Criteria;
use App\Repositories\Repository;
use App\User;

class SelfScope extends Criteria
{
    /**
     * @var User
     */
    protected $user;

    /**
     * FriendsScope constructor.
     * @param $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $model
     * @return mixed
     */
    public  function apply($model)
    {
        $query = $model->where('creator_id', '=', $this->user->id);
        return $query;
    }

}