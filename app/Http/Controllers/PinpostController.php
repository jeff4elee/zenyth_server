<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataValidator;
use Illuminate\Http\Request;
use App\Pinpost;
use App\User;
use App\Entity;
use App\EntitysPicture;
use App\Image;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * Class PinpostController
 * @package App\Http\Controllers
 */
class PinpostController extends Controller
{

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

        $validator = DataValidator::validatePinpost($request);
        if ($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 400);

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

        /* Sets creator id */
        $api_token = $request->header('Authorization');
        $pin->creator_id = User::where('api_token', $api_token)->first()->id;

        $pin->save();

        return response(json_encode([
            'success' => true,
            'data' => [
                'pinpost' => $pin
                ]
        ]), 202);

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

        if ($pin == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['not found']
            ]), 404);
        }

        return response(json_encode([
            'success' => true,
            'data' => [
                'pinpost' => $pin
            ]
        ]), 202);

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

        $validator = Validator::make($request->all(), [
            'thumbnail' => 'image'
        ]);
        if ($validator->fails())
            return response(json_encode([
                'errors' => $validator->errors()->all()
            ]), 400);

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null) {
            return response(json_encode(['errors' => ['not found']]), 404);
        }

        /* Checks if pinpost being updated belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;

        if ($api_token != $request->header('Authorization')) {
            return response(json_encode(['errors' => ['Unauthenticated']])
                , 401);
        }

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

        return response(json_encode([
            'success' => true,
            'pinpost' => $pin
        ]), 202);

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

        if ($pin == null) {
            return response(json_encode(['errors' => ['not found']]), 404);
        }

        /* Checks if pinpost being deleted belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;
        if ($api_token != $request->header('Authorization')) {
            return response(json_encode(['errors' => ['Unauthenticated']])
                , 401);
        }

        $pin->thumbnail->delete();
        $pin->entity->delete();

        return response(json_encode([
            'success' => true
        ]), 202);

    }

}
