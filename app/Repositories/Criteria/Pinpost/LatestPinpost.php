<?php

namespace App\Repositories\Criteria\Pinpost;

use App\Repositories\Criteria\Criteria;

class LatestPinpost extends Criteria
{
    public  function apply($model)
    {
        $query = $model->latest();
        return $query;
    }
}