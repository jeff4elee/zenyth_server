<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pinpost;
use App\User;
use App\Entity;
use App\Image;
use App\Http\Controllers\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class PinpostController extends Controller
{

    public function create(Request $request)
    {

        $validator = $this->validator($request);
        if($validator->fails()) {
            return $validator->errors()->all();
        }

        $pin = new Pinpost();

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');

        $image = new Image();
        if($request->file('thumbnail') != null) {
            $this->storeThumbnail($request->file('thumbnail'), $image);
            $image->save();
        }

        $pin->thumbnail_id = $image->id;

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

        $validator = Validator::make($request->all(), [
            'thumbnail' => 'image'
        ]);
        if($validator->fails()) {
            return $validator->errors()->all();
        }

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

        if ($request->file('thumbnail') != null) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            $this->storeThumbnail($request->file('thumbnail'), $image);

            Storage::disk('images')->delete($old_filename);
            $image->update();

        }

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

        $pin->thumbnail->delete();
        $pin->entity->delete();

        return response(json_encode(['pinpost status' => 'deleted'])
                        , 200);

    }

    protected function validator(Request $request) {

        return Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'thumbnail' => 'image'
        ]);

    }

    protected function storeThumbnail(UploadedFile $file, Image $image)
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
