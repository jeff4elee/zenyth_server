<?php

namespace App\Http\Controllers;

use App\EntitysPicture;
use Illuminate\Http\Request;
use App\Pinvite;
use App\Entity;
use App\User;
use App\Image;
use App\Http\Controllers\ImageController;
use App\Http\Requests\DataValidator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Class PinviteController
 * @package App\Http\Controllers
 */
class PinviteController extends Controller
{

    /**
     * Creates a Pinvite, storing thumbnail image if there is any
     *
     * @param Request $request, post request
     *        rules: requires title, description, latitude,
     *          longitude, event_time
     * @return Pinvite information
     */
    public function create(Request $request)
    {

        $validator = DataValidator::validatePinvite($request);
        if ($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $pin = new Pinvite();
        $entity = Entity::create([]);

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');
        $pin->event_time = $request->input('event_time');

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

        $api_token = $request->header('Authorization');
        $pin->creator_id = User::where('api_token', $api_token)->first()->id;

        $pin->save();

        return response(json_encode([
            'success' => true,
            'data' => $pin
        ]), 200);

    }

    /**
     * Gives back information on Pinvite
     *
     * @param $pinvite_id
     * @return pin information, json response if pinvite not found
     */
    public function read($pinvite_id)
    {

        $pin = Pinvite::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['not found']
            ]), 404);
        }

        return response(json_encode([
            'success' => true,
            'data' => $pin
        ]), 200);

    }

    /**
     * Updates Pinvite with information
     *
     * @param Request $request, post request
     * @param $pinvite_id
     * @return pin information, json response if failed
     */
    public function update(Request $request, $pinvite_id)
    {

        $validator = Validator::make($request->all(), [
            'thumbnail' => 'image'
        ]);
        if ($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        /* Checks if pinvite is there */
        $pin = Pinvite::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['not found']
            ]), 200);
        }

        /* Checks if pinvite being updated belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;

        $headerToken = $this->stripBearerFromToken($request->header('Authorization'));

        if ($api_token != $headerToken) {
            return response(json_encode([
                'success' => false,
                'errors' => ['Unauthenticated']
                ])
                , 401);
        }

        if ($request->has('title'))
            $pin->title = $request->get('title');

        if ($request->has('description'))
            $pin->description = $request->get('description');

        if ($request->file('thumbnail') != null) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            ImageController::storeImage($request->file('thumbnail'), $image);

            Storage::disk('images')->delete($old_filename);
            $image->update();
        }

        if ($request->has('latitude'))
            $pin->latitude = $request->get('latitude');

        if ($request->has('longitude'))
            $pin->longitude = $request->get('longitude');

        if ($request->has('event_time'))
            $pin->event_time = $request->get('event_time');

        $pin->update();

        return response(json_encode([
            'success' => true,
            'data' => $pin
        ]), 200);

    }

    /**
     * Deletes the pinvite
     *
     * @param Request $request, delete request
     * @param $pinvite_id
     * @return json response
     */
    public function delete(Request $request, $pinvite_id)
    {

        /* Checks if pinvite is there */
        $pin = Pinpost::find($pinvite_id);

        if ($pin == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['not found']
            ]), 404);
        }

        /* Checks if pinvite being deleted belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;

        $headerToken = $this->stripBearerFromToken($request->header('Authorization'));

        if ($api_token != $headerToken) {
            return response(json_encode([
                'success' => false,
                'errors' => ['Unauthenticated']
                ])
                , 200);
        }


        $pictures = $pin->entity->pictures;
        foreach ($pictures as $picture) {
            $picture->image->delete();
        }
        $pin->entity->delete();


        return response(json_encode([
            'success' => true
        ]), 200);

    }

    /**
     * Uploads picture to pinvite
     *
     * @param Request $request post request,
     *        rules: file must be image
     * @param $pinvite_id
     * @return json response
     */
    public function uploadPicture(Request $request, $pinvite_id)
    {

        $validator = DataValidator::validatePicture($request);
        if ($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $file = $request->file('image');
        $pin = Pinvite::find($pinvite_id)->id;

        if ($file != null) {
            $image = new Image();
            ImageController::storeImage($file, $image);
            $image->save();
            $picture = new EntitysPicture();
            $picture->entity_id = $pin->entity_id;
            $picture->image_id = $image->id;
            $picture->save();
            return response(json_encode(['success' => true]), 200);
        }

    }

    /**
     * Deletes picture from pinvite
     *
     * @param $pinvite_picture_id
     */
    public function deletePicture($image_id)
    {

        $picture = EntitysPicture::find($image_id);
        $picture->image->delete();
        return response(['success' => true], 202);

    }

    public function pinvitePictures($pinvite_id)
    {

        $pin = Pinvite::find($pinvite_id);
        return $pin->entity->pictures;

    }

}
