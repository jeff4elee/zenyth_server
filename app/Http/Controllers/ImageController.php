<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Image;
use App\Repositories\ImageRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImageController
 * @package App\Http\Controllers
 */
class ImageController extends Controller
{
    private $imageRepo;

    function __construct(ImageRepository $imageRepo)
    {
        $this->imageRepo = $imageRepo;
    }

    /**
     * Store image into storage, image name is a random string of length 32
     * @param UploadedFile $file, file that was uploaded
     * @param Image $image, image model to be populated
     * @param $directory
     * @return $image
     */
    static public function storeImageByUploadedFile(UploadedFile $file, $image,
                                                    $directory = 'images')
    {
        if($directory == null)
            $directory = 'images';

        $extension = $file->extension();
        $filename = self::generateImagename($extension, $image);

        Storage::disk($directory)->put($filename, File::get($file));
        return $filename;
    }

    /**
     * Store a profile image into storage
     * @param $url
     * @param $directory
     * @return Image|mixed|null|\Psr\Http\Message\ResponseInterface
     */
    static public function storeImageByUrl($url, $image, $directory = 'images')
    {
        if($url == null)
            Exceptions::invalidRequestException();
        if($directory == null)
            $directory = 'images';

        $mimeTypes = array(
            'image/png' => 'png',
            'image/jpg' => 'jpeg',
            'image/jpeg' => 'jpeg',
            'image/gif' => 'gif'
        );

        try {
            $client = new Client();
            $imageFile = $client->request('GET', $url);
            $contentType = strtolower($image->getHeader('Content-Type')[0]);
            $extension = $mimeTypes[$contentType];

            if($extension == null)
                Exceptions::invalidImageTypeException(INVALID_IMAGE_TYPE);

            $filename = self::generateImageName($extension, $image);
            Storage::disk($directory)->put($filename, $imageFile->getBody());

            return $filename;
        } catch (\Exception $error) {
            // Log the error or something
            Log::info($error);
            return null;
        }
    }

    public function uploadImage(Request $request, $imageable_id)
    {
        $user = $request->get('user');
        $image = $request->file('image');
        $request->merge([
            'user_id' => $user->id,
            'image_file' => $image,
            'directory' => $this->getDirectory($request),
            'imageable_id' => $imageable_id,
            'imageable_type' => $this->getImageableType($request)
        ]);
        $this->imageRepo->create($request);
        return Response::successResponse(UPLOAD_SUCCESS);
    }

    public function deleteImage(Request $request, $image_id)
    {
        $user = $request->get('user');
        $request->merge(['user_id', $user->id]);
        $this->imageRepo->delete($request, $image_id);
        return Response::successResponse(DELETE_SUCCESS);
    }

    /**
     * Show the Image
     * @param $image_id
     * @return mixed, an image response
     */
    public function showImage($image_id)
    {
        $image = $this->imageRepo->read($image_id);
        if($image == null)
            Exceptions::notFoundException(NOT_FOUND);

        $path = 'app/images/' . $image->filename;
        return Response::rawImageResponse($path);
    }

    /**
     * Generate a unique image name
     * @param $extension
     * @param $image
     * @return string
     */
    static public function generateImageName($extension, $image)
    {
        do {

            $filename = str_random(32)."_".$image->id.".".$extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while ($dup_filename != null);

        return $filename;
    }

    public function getImageableType(Request $request)
    {
        if($request->is('api/pinpost/upload_image/*'))
            return 'App\Pinpost';
        else if($request->is('api/comment/upload_image/*'))
            return 'App\Comment';
        else if($request->is('api/profile/upload_image/*'))
            return 'profile_pictures';

        return null;
    }

    public function getDirectory(Request $request)
    {
        if($request->is('api/pinpost/upload_image/*'))
            return 'images';
        else if($request->is('api/comment/upload_image/*'))
            return 'images';
        else if($request->is('api/profile/upload_image/*'))
            return 'profile_pictures';
    }

}