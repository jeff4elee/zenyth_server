<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinpost;
use App\User;
use App\Entity;
use App\Http\Controllers\Response;

class PinpostController extends Controller
{

    public function create(Request $request)
    {

        $pin = new Pinpost();

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->thumbnail = $request->input('thumbnail');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');

        $pin->entity_id = Entity::create([])->id;

        $api_token = $request->header('Authorization');
        $pin->user_id = User::where('api_token', $api_token)->first()->id;

        $pin->save();

        return $pin;

    }

    public function read($pinpost_id)
    {

        $pin = Pinpost::find($pinpost_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        return $pin;

    }

    public function update(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        /* Checks if pinpost being updated belongs to the user making the
            request */
        $api_token = $pin->user->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
        }

        if ($request->has('title'))
            $pin->title = $request->input('title');

        if ($request->has('description'))
            $pin->description = $request->input('description');

        if ($request->has('thumbnail'))
            $pin->thumbnail = $request->input('thumbnail');

        if ($request->has('latitude'))
            $pin->latitude = $request->input('latitude');

        if ($request->has('longitude'))
            $pin->longitude = $request->input('longitude');

        $pin->update();

        return $pin;

    }

    public function delete(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null) {
            return response(json_encode(['error' => 'not found']), 404);
        }

        /* Checks if pinpost being updated belongs to the user making the
            request */
        $api_token = $pin->user->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
        }

        $pin->delete();

        return response(json_encode(['pinpost status' => 'deleted'])
                        , 200);

    }

    public function likesCount($pinpost_id)
    {

        return Pinpost::find($pinpost_id)->entity->likesCount();

    }

    public function commentsCount($pinpost_id)
    {

        return Pinpost::find($pinpost_id)->entity->commentsCount();

    }

}
