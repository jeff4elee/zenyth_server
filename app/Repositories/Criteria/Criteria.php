<?php

namespace App\Repositories\Criteria;

abstract class Criteria
{
    /**
     * Apply a criteria
     * @param $model
     * @return mixed
     */
    public abstract function apply($model);
}