<?php

namespace App\Repositories\Criteria\Pinpost;

use App\Repositories\Criteria\Criteria;
use App\User;

/**
 * Class FriendsScope
 * @package App\Repositories\Criteria\Pinpost
 */
class FriendsScope extends Criteria
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
    public function apply($model)
    {
        $friendsId = $this->user->friendsId();

        // All id's of friends
        $idsToInclude = array_values($friendsId);

        // Put the current user's id in the array to query
        array_push($idsToInclude, $this->user->id);

        $query = $model->whereIn('creator_id', $idsToInclude);

        return $query;
    }

}