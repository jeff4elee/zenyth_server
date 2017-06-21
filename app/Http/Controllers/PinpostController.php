<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinpost;

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
        $pin->user_id = User::where('api_token', '=', $request->get('api_token'))->first()->id;

        $pin->save();

        return 1;

    }

    public function read($entity_id)
    {

        $pin = Entity::find($entity_id)->first()->pinpost;

        if ($pin == null) {
            return 0;
        }

        return $pin;

    }

    public function update(Request $request, $entity_id)
    {

        $pin = Entity::find($entity_id)->first()->pinpost;

        if ($pin == null) {
            return 0;
        }

        if ($request->has('title'))
            $pin->title = $request->get('title');

        if ($request->has('description'))
            $pin->description = $request->get('description');

        if ($request->has('thumbnail'))
            $pin->thumbnail = $request->get('thumbnail');

        if ($request->has('latitude'))
            $pin->latitude = $request->get('latitude');

        if ($request->has('longitude'))
            $pin->longitude = $request->get('longitude');

        $pin->update();

        return 1;

    }

    public function delete($entity_id)
    {

        $pin = Entity::find($entity_id)->first()->pinpost;

        if ($pin == null) {
            return 0;
        }

        $pin->delete();

        return 1;

    }

}