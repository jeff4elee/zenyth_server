<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinvite;
use Illuminate\Support\Facades\Validator;
use App\Entity;
use App\User;
use App\Image;
use App\Pinvite_picture;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Response;

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
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');
        $pin->event_time = $request->input('event_time');

        $image = new Image();
        if($request->file('thumbnail') != null) {
            $this->storeImage($request->file('thumbnail'), $image);
            $image->save();
        }

        $pin->thumbnail_id = $image->id;
        $pin->entity_id = Entity::create([])->id;

        $api_token = $request->header('Authorization');
        $pin->creator_id = User::where('api_token', $api_token)->first()->id;

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
        $api_token = $pin->creator->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
        }

        if ($request->has('title'))
            $pin->title = $request->get('title');

        if ($request->has('description'))
            $pin->description = $request->get('description');

        if ($request->file('thumbnail') != null) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            $this->storeImage($request->file('thumbnail'), $image);

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
        $api_token = $pin->creator->api_token;

        if($api_token != $request->header('Authorization')) {
            return response(json_encode(['error' => 'Unauthenticated'])
                            , 401);
        }

        $pin->entity->delete();
        $pin->thumbnail->delete();
        $pictures = $pin->pinvite_pictures;
        foreach($pictures as $picture) {
            $picture->image->delete();
        }

        return response(json_encode(['pinvite status' => 'deleted'])
                        , 200);

    }

    public function uploadPicture(Request $request, $pinvite_id)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'image'
        ]);

        if($validator->fails()) {
            return $validator->errors()->all();
        }

        $file = $request->file('image');

        if($file != null) {
            $image = new Image();
            $this->storeImage($file, $image);
            $image->save();
            $picture = new Pinvite_picture();
            $picture->pinvite_id = $pinvite_id;
            $picture->image_id = $image->id;
            $picture->save();
            return response(json_encode(['pinvite_pictures' => 'uploaded'])
                , 200);
        }

    }

    public function deletePicture($pinvite_picture_id)
    {
        $picture = Pinvite_picture::find($pinvite_picture_id);
        $picture->image->delete();
    }

    protected function validator(Request $request)
    {

        return Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'thumbnail' => 'image|size:15000',
            'event_time' => 'required'
        ]);

    }

    protected function storeImage(UploadedFile $file, Image $image)
    {

        $extension = $file->extension();

        do {

            $filename = str_random(45) . "." . $extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while($dup_filename != null);

        Storage::disk('images')->put($filename, File::get($file));
        $image->filename = $filename;
        $image->path = Storage::disk('images')->url($filename);

    }
    
}
