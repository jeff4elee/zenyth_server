<?php

namespace App\Http\Traits;

trait MathHelper
{
    public function distance($firstCoord, $secondCoord, $unit)
    {
        $lat1 = $firstCoord[0];
        $long1 = $firstCoord[1];
        $lat2 = $secondCoord[0];
        $long2 = $secondCoord[1];

        $theta = $long1 - $long2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1))
            * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "KM")
            return ($miles * 1.609344);
        else
            return $miles;
    }
}