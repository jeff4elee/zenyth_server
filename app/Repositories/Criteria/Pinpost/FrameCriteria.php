<?php

namespace App\Repositories\Criteria\Pinpost;

use App\Repositories\Criteria\Criteria;
use App\Repositories\Repository;

class FrameCriteria extends Criteria
{
    /**
     * @var array
     */
    protected $areaData;

    /**
     * FrameCriteria constructor.
     * @param array $areaData
     */
    public function __construct(array $areaData)
    {
        $this->areaData = $areaData;
    }

    /**
     * @param $model
     * @return mixed
     */
    public  function apply($model)
    {
        $firstCoord = explode(",", $this->areaData['first_coord']);
        $secondCoord = explode(",", $this->areaData['second_coord']);

        // The following logic is used to get the smaller and the larger of the
        // latitude and longitude so we can form one query. This is done so that
        // the user does not have to specifically specify which corner the
        // coordinate is
        if($firstCoord[0] > $secondCoord[0]) {
            $smallLat = $secondCoord[0];
            $largeLat = $firstCoord[0];
        } else {
            $smallLat = $firstCoord[0];
            $largeLat = $secondCoord[0];
        }

        if($firstCoord[1] > $secondCoord[1]) {
            $smallLong = $secondCoord[1];
            $largeLong = $firstCoord[1];
        } else {
            $smallLong = $firstCoord[1];
            $largeLong = $secondCoord[1];
        }

        // Get all pinposts inside the box
        $query = $model->where([
            ['latitude', '>=', $smallLat],
            ['latitude', '<=', $largeLat],
            ['longitude', '>=', $smallLong],
            ['longitude', '<=', $largeLong]
        ]);

        return $query;
    }
}