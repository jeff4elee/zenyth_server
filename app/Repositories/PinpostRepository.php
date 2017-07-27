<?php

namespace App\Repositories;

use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Image;
use Illuminate\Container\Container as App;
use Illuminate\Http\Request;

class PinpostRepository extends Repository
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

        if($pin)
            return $pin;
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
        return $pin;
    }
}