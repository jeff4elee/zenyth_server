<?php

namespace App\Http\Controllers;

use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Image;
use App\Pinpost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class PinpostController
 * @package App\Http\Controllers
 */
class PinpostController extends Controller
{
    use AuthenticationTrait;

    /**
     * Creates a Pinpost, storing thumbnail image if there is any
     *
     * @param Request $request, post request
     *        rules: requires title, description, latitude,
     *          longitude, event_time
     * @return Pinpost information
     */
    public function create(Request $request)
    {
        $pin = new Pinpost();
        $entity = Entity::create([]);

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');

        /* Checks if a thumbnail was provided */
        if ($request->file('thumbnail') != null) {
            $image = new Image();
            $entitys_picture = new EntitysPicture();
            ImageController::storeImage($request->file('thumbnail'), $image);
            $image->save();
            $pin->thumbnail_id = $image->id;
            $entitys_picture->entity_id = $entity->id;
            $entitys_picture->image_id = $image->id;
            $entitys_picture->save();
        }

        $pin->entity_id = $entity->id;

        $user = $request->get('user');
        $pin->creator_id = $user->id;

        $pin->save();

        return Response::dataResponse(true, ['pinpost' => $pin],
            'successfully created pinpost');

    }

    /**
     * Gives back information on Pinpost
     *
     * @param $pinpost_id
     * @return pin information, json response if pinpost not found
     */
    public function read($pinpost_id)
    {

        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        return Response::dataResponse(true, ['pinpost' => $pin]);

    }

    /**
     * Updates Pinpost with information
     *
     * @param Request $request, post request
     * @param $pinvite_id
     * @return pin information, json response if failed
     */
    public function update(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        /* Checks if pinpost being updated belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        /* Updates title */
        if ($request->has('title'))
            $pin->title = $request->input('title');

        /* Updates description */
        if ($request->has('description'))
            $pin->description = $request->input('description');

        /* Updates thumbnail */
        if ($request->file('thumbnail') != null) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            ImageController::storeImage($request->file('thumbnail'), $image);

            if($old_filename != null)
                Storage::disk('images')->delete($old_filename);
            $image->update();
        }

        /* Updates latitude */
        if ($request->has('latitude'))
            $pin->latitude = $request->input('latitude');

        /* Updates longitude */
        if ($request->has('longitude'))
            $pin->longitude = $request->input('longitude');

        $pin->update();

        return Response::dataResponse(true, ['pinpost' => $pin],
            'Successfully updated pinpost');

    }

    /**
     * Deletes the pinpost
     *
     * @param Request $request, delete request
     * @param $pinpost_id
     * @return json response
     */
    public function delete(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        /* Checks if pinpost being deleted belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        $pin->thumbnail->delete();
        $pin->entity->delete();

        return Response::successResponse('Successfully deleted pinpost');

    }

    /**
     * Fetches all pinposts of friends ordered by latest first
     *
     * @param Request $request
     * @return mixed
     */
    public function fetchPost(Request $request)
    {

        $user = $request->get('user');
        $idArray = $user->friendsId();

        // Get all pinposts that belong to friends
        $pinposts = Pinpost::select('*')
            ->whereIn('creator_id', $idArray)
            ->latest()->get();

        return Response::dataResponse(true, ['pinposts' => $pinposts]);

    }

}
