<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinpost;
use App\User;
use App\Entity;

class PinpostController extends Controller
{

    public function create(Request $request)
    {

        $pin = new Pinpost();

        $pin->title = $request->get('title');
        $pin->description = $request->get('description');
        $pin->thumbnail = $request->get('thumbnail');
        $pin->latitude = $request->get('latitude');
        $pin->longitude = $request->get('longitude');

        $pin->entity_id = Entity::create([])->id;

        $api_token = $request->header('Authorization');
        //return response(json_encode(['api_token' => $api_token]), 200);
        $pin->user_id = User::where('api_token', $api_token)->first()->id;

        $pin->save();

        return $pin;

    }

    public function read($entity_id)
    {

        $pin = Pinpost::where('entity_id', '=', $entity_id)->first();

        if ($pin == null) {
            return 0;
        }

        return $pin;

    }

    public function update(Request $request, $entity_id)
    {

        //return $request->getContent();
        $pin = Pinpost::where('entity_id', '=', $entity_id)->first();

        if ($pin == null) {
            return 0;
        }

        if ($request->has('title'))
            $pin->title = $request->get('title');

        if ($request->has('description'))
            $pin->description = $request->input('description');

        if ($request->has('thumbnail'))
            $pin->thumbnail = $request->get('thumbnail');

        if ($request->has('latitude'))
            $pin->latitude = $request->get('latitude');

        if ($request->has('longitude'))
            $pin->longitude = $request->get('longitude');

        $pin->update();

        return $pin;

    }

    public function delete($entity_id)
    {

        $pin = Pinpost::where('entity_id', '=', $entity_id)->first();

        if ($pin == null) {
            return 0;
        }

        $pin->delete();

        return 1;

    }

}
