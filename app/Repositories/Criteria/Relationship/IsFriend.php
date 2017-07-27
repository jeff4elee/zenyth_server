<?php


namespace App\Repositories\Criteria\Relationship;

use App\Repositories\Criteria\Criteria;

class IsFriend extends Criteria
{
    public function apply($model)
    {
        $query = $model->where('status', '=', true);
        return $query;
    }
}