<?php

namespace App\Repositories\Criteria;

interface CriteriaInterface
{
    /**
     * Skip applying the criteria
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true);

    /**
     * Return criteria
     * @return mixed
     */
    public function getCriteria();

    /**
     * Return specific criteria
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria);

    /**
     * Push a criteria onto a collection
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * Apply criteria to the model
     * @return $this
     */
    public function  applyCriteria();
}