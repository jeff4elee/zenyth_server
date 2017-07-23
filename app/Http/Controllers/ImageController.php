<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as InterventionImage;
use App\Image;
use App\Profile;
use GuzzleHttp\Client;

/**
 * Class ImageController
 * @package App\Http\Controllers
 */
class ImageController extends Controller
{

    /**
     * Stores image into storage, image name is a random string of length 32
     *
     * @param UploadedFile $file, file that was uploaded
     * @param Image $image, image model to be populated
     */
    static public function storeImage(UploadedFile $file, Image $image, $directory = 'images')
    {

        $extension = $file->extension();

        $filename = ImageController::generateFilename($extension);

        Storage::disk($directory)->put($filename, File::get($file));
        $image->filename = $filename;

    }

    static public function storeProfileImage($url)
    {
        if($url == null)
            return null;

        $mimeTypes = array(
            'image/png' => 'png',
            'image/jpg' => 'jpeg',
            'image/jpeg' => 'jpeg',
            'image/gif' => 'gif'
        );

        try {
            $client = new Client();
            $image = $client->request('GET', $url);
            $contentType = strtolower($image->getHeader('Content-Type')[0]);
            $extension = $mimeTypes[$contentType];

            if($extension == null) {
                return null;
            }

            $filename = ImageController::generateImageName($extension);

            Storage::disk('profile_pictures')->put($filename, $image->getBody());

            $image = new Image();
            $image->filename = $filename;
            $image->save();

            return $image;
        } catch (Exception $error) {
            // Log the error or something
            Log::info($error);
            return null;
        }
    }

    /**
     * Shows the Image
     *
     * @param $filename, name of image file
     * @return mixed, an image response
     */
    public function showImage($image_id)
    {
        $image = Image::find($image_id);
        if($image == null)
            return Response::errorResponse(Exceptions::notFoundException(), 'Image not found');
        $path = 'app/images/' . $image->filename;
        return Response::rawImageResponse($path);
    }

    public function showProfileImage($user_id)
    {
        $profile = Profile::where('user_id', '=', $user_id)->first();
        $image = $profile->profilePicture;
        if($image == null) {
            return response(json_encode([
                'success' => true,
                'data' => [
                    'message' => 'No profile picture'
                ]
            ]), 200);
        }
        $filename = $image->filename;
        $path = 'app/profile_pictures/' . $filename;
        return Response::rawImageResponse($path);
    }

    static public function generateImageName($extension)
    {
        do {

            $filename = str_random(32) . "." . $extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while ($dup_filename != null);

        return $filename;
    }

}