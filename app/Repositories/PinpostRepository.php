<?php

namespace App\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Support\Collection;

class PinpostRepository extends Repository
                        implements PinpostRepositoryInterface
{

    /**
     * Specify Model class name
     * @return mixed
     */
    function model()
    {
        return 'App\Pinpost';
    }

    /**
     * Get all pinposts in a rectangular box
     * @param $areaData , contain keys [top_left, bottom_right]
     * @return mixed
     */
    public function pinpostsInFrame($areaData)
    {
        $topLeft = explode(",", $areaData['top_left']);
        $bottomRight = explode(",", $areaData['bottom_right']);

        $lat1 = $topLeft[0];
        $long1 = $topLeft[1];
        $lat2 = $bottomRight[0];
        $long2 = $bottomRight[1];

        // $long1 will always be less than $long2 unless we're at the edge
        // where the left is positive and the right is negative
        if($long1 > $long2) {
            $query = $this->model->where([
                ['latitude', '>=', $lat1],
                ['latitude', '<=', $lat2],
                ['longitude', '<=', $long1],
                ['longitude', '>=', $long2]
            ]);
        }
        else {
            $query = $this->model->where([
                ['latitude', '>=', $lat1],
                ['latitude', '<=', $lat2],
                ['longitude', '>=', $long1],
                ['longitude', '<=', $long2]
            ]);
        }

        $this->model = $query;
        return $this;
    }

    /**
     * Get all pinposts in a radius
     * @param $areaData , contain keys [center, radius]
     * @return mixed
     */
    public function pinpostsInRadius($areaData)
    {
        $radius = $areaData['radius'];

        $center = explode(",", $areaData['center']);
        $centerLat = $center[0];
        $centerLong = $center[1];

        // Haversine formula to determine distance
        if(strtolower($areaData['unit']) == 'km')
            $query = $this->model->whereRaw(
                "2 * ASIN( SQRT( POW( SIN( ( RADIANS(latitude) - RADIANS( ? ) )/2 ) , 2 ) +
                 COS( RADIANS( ? ) ) * COS( RADIANS( latitude ) ) *
                  POW( SIN( ( RADIANS(longitude) - RADIANS( ? ))/2 ) , 2 ) ) ) * 6371 <= ?",
                [$centerLat, $centerLat, $centerLong , $radius]);
        else
            $query = $this->model->whereRaw(
                "2 * ASIN( SQRT( POW( SIN( ( RADIANS(latitude) - RADIANS( ? ) )/2 ) , 2 ) +
                 COS( RADIANS( ? ) ) * COS( RADIANS( latitude ) ) *
                  POW( SIN( ( RADIANS(longitude) - RADIANS( ? ))/2 ) , 2 ) ) ) * 6371 * 0.621371 <= ?",
                [$centerLat, $centerLat, $centerLong , $radius]);

        $this->model = $query;
        return $this;
    }

    /**
     * Get pinposts with scopes [self, following, public]
     * @param $scope
     * @param $user
     * @return mixed
     */
    public function pinpostsWithScope($scope, $user, $includeSelf = true)
    {
        $scope = strtolower($scope);
        if($scope == 'self') {
            $query = $this->model->where('user_id', '=', $user->id);
            $this->model = $query;
        }
        else if($scope == 'following') {
            $followingIds = $user->followingIds();

            // All id's of friends
            $idsToInclude = array_values($followingIds);

            if($includeSelf) {
                // Put the current user's id in the array to query
                array_push($idsToInclude, $user->id);
            }

            $query = $this->model->whereIn('user_id', $idsToInclude);
            $this->model = $query;
        }
        else {
            if(!$includeSelf) {
                $query = $this->model->where('user_id', '!=', $user->id);
                $this->model = $query;
            }
        }
        return $this;
    }

    public function filterByPrivacy($user, $pinposts)
    {
        $filteredPinposts = new Collection();

        $userFollowingIds = $user->followingIds();
        //dd($userFollowingIds);

        foreach($pinposts as $pinpost) {

            if($pinpost->privacy == 'public') {
                $filteredPinposts->push($pinpost);
            }

            else if($pinpost->privacy == 'self') {
                $creatorId = $pinpost->user_id;
                if($creatorId == $user->id)
                    $filteredPinposts->push($pinpost);
            }

            else if($pinpost->privacy == 'followers') {
                $creatorId = $pinpost->user_id;

                // If user is in the list of followers of the creatorId, include
                // the pinpost
                if(in_array($creatorId, $userFollowingIds))
                    $filteredPinposts->push($pinpost);
            }

        }

        return $filteredPinposts;
    }
}