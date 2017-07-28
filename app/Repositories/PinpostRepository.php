<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use Illuminate\Container\Container as App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    public function create(Request $request)
    {
        $entity = $request->get('entity');
        $user = $request->get('user');

        $pin = $this->model->create([
            'title' => $request['title'],
            'description' => $request['description'],
            'latitude' => (double)$request['latitude'],
            'longitude' => (double)$request['longitude'],
            'entity_id' => $entity->id,
            'creator_id' => $user->id
        ]);

        if ($pin) {
            $key = 'pinpost' . $pin->id;
            Cache::put($key, $pin);
            return $pin;
        }

        else
            Exceptions::unknownErrorException('Error creating pinpost');

    }

    public function update(Request $request, $id, $attribute = 'id')
    {
        // Check if pinpost is there
        $pin = $this->model->where($attribute, '=', $id)->first();
        if (!$pin)
            Exceptions::notFoundException('Pinpost not found');

        // Check if pinpost being updated belongs to the user making the
        // request
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        if($request->has('title'))
            $pin->title = $request['title'];
        if($request->has('description'))
            $pin->description = $request['description'];
        if($request->has('latitude'))
            $pin->latitude = (double)$request['latitude'];
        if($request->has('description'))
            $pin->longitude = (double)$request['longitude'];

        $pin->update();

        $key = 'pinpost' . $pin->id;
        Cache::put($key, $pin);

        return $pin;
    }

    /**
     * Get all pinposts in a rectangular box
     * @param $areaData , contain keys [first_coord, second_coord]
     * @return mixed
     */
    public function pinpostsInFrame($areaData)
    {
        $firstCoord = explode(",", $areaData['first_coord']);
        $secondCoord = explode(",", $areaData['second_coord']);

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
        $query = $this->model->where([
            ['latitude', '>=', $smallLat],
            ['latitude', '<=', $largeLat],
            ['longitude', '>=', $smallLong],
            ['longitude', '<=', $largeLong]
        ]);

        $this->model = $query;
        return $this;
    }



    public function read($id, $fields = ['*'])
    {

        $key = 'pinpost' . $id;

        if (Cache::has($key)) {
            return Cache::get($key);
        } else {
            $pin = parent::read($id, $fields);
            Cache::put($key, $pin);
            return $pin;
        }

    }

    public function delete(Request $request, $id)
    {
        $key = 'pinpost' . $id;

        if (Cache::has($key)) {
            Cache::forget($key);
        }

        parent::delete($request, $id);

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

        if(strtolower($areaData['unit']) == 'km')
            $query = $this->model->whereRaw(
                "( (SQRT( POW( (latitude - ?), 2) +  POW( (longitude - ?), 2) ) ) * 69.09 * 1.609344 ) <= ?",
                [$centerLat, $centerLong, $radius]);
        else
            $query = $this->model->whereRaw(
                "( (SQRT( POW( (latitude - ?), 2) +  POW( (longitude - ?), 2) ) ) * 69.09 ) <= ?",
                [$centerLat, $centerLong, $radius]);

        $this->model = $query;
        return $this;
    }

    /**
     * Get pinposts with scopes [self, friends, public]
     * @param $scope
     * @param $user
     * @return mixed
     */
    public function pinpostsWithScope($scope, $user)
    {

        $scope = strtolower($scope);
        if($scope == 'self') {
            $query = $this->model->where('creator_id', '=', $user->id);
            $this->model = $query;
            return $this;
        }
        else if($scope == 'friends') {
            $friendsId = $user->friendsId();

            // All id's of friends
            $idsToInclude = array_values($friendsId);

            // Put the current user's id in the array to query
            array_push($idsToInclude, $user->id);

            $query = $this->model->whereIn('creator_id', $idsToInclude);
            $this->model = $query;
            return $this;
        }

    }
}