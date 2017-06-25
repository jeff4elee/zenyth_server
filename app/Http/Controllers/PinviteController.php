<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinvite;
use Illuminate\Support\Facades\Validator;
use App\Entity;
use App\User;

class PinviteController extends Controller
{

    public function create(Request $request)
    {

        $validator = $this->validator($request);
        if($validator->fails()) {
            return $validator->errors()->all();
        }

        $pin = new Pinvite();

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->thumbnail = $request->input('thumbnail');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');
        $pin->event_time = $request->input('event_time');

        $pin->entity_id = Entity::create([])->id;

        $api_token = $request->header('Authorization');
        $pin->user_id = User::where('api_token', $api_token)->first()->id;

        $pin->save();

        return $pin;

    }

    public function read($pinvite_id)
    {

        $pin = Pinvite::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        return $pin;

    }

    public function update(Request $request, $pinvite_id)
    {

        $validator = $this->validator($request);
        if($validator->fails()) {
            return $validator->errors()->all();
        }

        /* Checks if pinvite is there */
        $pin = Pinvite::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        /* Checks if pinvite being updated belongs to the user making the
            request */
        $api_token = $pin->user->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
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

        if ($request->has('event_time'))
            $pin->event_time = $request->get('event_time');

        $pin->update();

        return $pin;

    }

    public function delete(Request $request, $pinvite_id)
    {

        /* Checks if pinvite is there */
        $pin = Pinpost::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        /* Checks if pinvite being updated belongs to the user making the
            request */
        $api_token = $pin->user->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
        }

        $pin->entity->delete();

        return response(json_encode(['pinvite status' => 'deleted'])
                        , 200);

    }

    protected function validator(Request $request) {

        return Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'event_time' => 'required'
        ]);

    }
    
}
