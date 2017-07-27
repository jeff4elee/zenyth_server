<?php

namespace App\Repositories\Criteria\Pinpost;

use App\Repositories\Criteria\Criteria;

class RadiusCriteria extends Criteria
{
    /**
     * @var array
     */
    protected $areaData;

    public function __construct(array $areaData)
    {
        $this->areaData = $areaData;
    }

    /**
     * Apply a criteria
     * @param $model
     * @return mixed
     */
    public function apply($model)
    {
        $radius = $this->areaData['radius'];

        $center = explode(",", $this->areaData['center']);
        $centerLat = $center[0];
        $centerLong = $center[1];

        if(strtolower($this->areaData['unit']) == 'km')
            $query = $model->whereRaw(
                "( (SQRT( POW( (latitude - ?), 2) +  POW( (longitude - ?), 2) ) ) * 69.09 * 1.609344 ) <= ?",
                [$centerLat, $centerLong, $radius]);
        else
            $query = $model->whereRaw(
                "( (SQRT( POW( (latitude - ?), 2) +  POW( (longitude - ?), 2) ) ) * 69.09 ) <= ?",
                [$centerLat, $centerLong, $radius]);


        return $query;
    }
}