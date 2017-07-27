<?php

namespace App\Repositories\Criteria\Relationship;

use App\Repositories\Criteria\Criteria;

class HaveRelationship extends Criteria
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int
     */
    protected $requesteeId;

    function __construct($userId, $requesteeId)
    {
        $this->userId = $userId;
        $this->requesteeId = $requesteeId;
    }

    public  function apply($model)
    {
        /* Verifies if they are already friends or if there is no pending
            request */
        $query = $model->where([
            ['requester', '=', $this->userId],
            ['requestee', '=', $this->requesteeId]
        ])->orWhere([
            ['requestee', '=', $this->userId],
            ['requester', '=', $this->requesteeId]
        ]);

        return $query;
    }
}