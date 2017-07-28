<?php


namespace App\Repositories;

/**
 * Interface PinpostRepositoryInterface
 * @package App\Repositories
 */
interface PinpostRepositoryInterface
{
    /**
     * Get all pinposts in a rectangular box
     * @param $areaData, contain keys [first_coord, second_coord]
     * @return mixed
     */
    public function pinpostsInFrame($areaData);

    /**
     * Get all pinposts in a radius
     * @param $areaData, contain keys [center, radius]
     * @return mixed
     */
    public function pinpostsInRadius($areaData);

    /**
     * Get pinposts with scopes [self, friends, public]
     * @param $scope
     * @param $user
     * @return mixed
     */
    public function pinpostsWithScope($scope, $user);
}