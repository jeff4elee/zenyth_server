<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Image;
use App\Repositories\CommentRepository;
use App\Repositories\ImageRepository;
use App\Repositories\PinpostRepository;
use App\Repositories\UserRepository;
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
    private $pinpostRepo;
    private $commentRepo;
    private $userRepo;

    function __construct(ImageRepository $imageRepo,
                        PinpostRepository $pinpostRepo,
                        CommentRepository $commentRepo,
                        UserRepository $userRepo)
    {
        $this->imageRepo = $imageRepo;
        $this->pinpostRepo = $pinpostRepo;
        $this->commentRepo = $commentRepo;
        $this->userRepo = $userRepo;
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
            // Create a client for a download request
            $client = new Client();
            $imageFile = $client->request('GET', $url);

            // Get image mime type
            $contentType = strtolower($image->getHeader('Content-Type')[0]);
            $extension = $mimeTypes[$contentType];

            if($extension == null)
                Exceptions::invalidImageTypeException(INVALID_IMAGE_TYPE);

            $filename = self::generateImageName($extension, $image);

            // getBody() method retrieves the raw image byte stream
            // Storage then writes it to a file
            Storage::disk($directory)->put($filename, $imageFile->getBody());

            return $filename;
        } catch (\Exception $error) {
            // Log the error or something
            Log::info($error);
            return null;
        }
    }

    /**
     * Upload an image
     * @param Request $request
     * @param $imageable_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request, $imageable_id)
    {
        $user = $request->get('user');
        $image = $request->file('image');
        $imageableType = $this->getImageableType($request);

        // Check if this imageable object exists
        $exist = $this->imageableExists($imageableType, $imageable_id);
        if(!$exist)
            Exceptions::notFoundException(NOT_FOUND);

        $request->merge([
            'user_id' => $user->id,
            'image_file' => $image,
            'directory' => $this->getDirectory($request),
            'imageable_id' => $imageable_id,
            'imageable_type' => $imageableType
        ]);
        $image = $this->imageRepo->create($request);
        return Response::dataResponse(true, [
            'image' => $image
        ]);
    }

    /**
     * Delete an image
     * @param Request $request
     * @param $image_id
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Concatenate image id to the end of the image
            $filename = str_random(32)."_".$image->id.".".$extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while ($dup_filename != null);

        return $filename;
    }

    /**
     * Get the type of imageable
     * @param Request $request
     * @return null|string
     */
    public function getImageableType(Request $request)
    {
        if($request->is('api/pinpost/upload_image/*'))
            return 'App\Pinpost';
        else if($request->is('api/comment/upload_image/*'))
            return 'App\Comment';
        else if($request->is('api/profile/profile_picture/*'))
            return 'App\Profile';

        return null;
    }

    /**
     * Get directory where the image should be stored
     * @param Request $request
     * @return string
     */
    public function getDirectory(Request $request)
    {
        if($request->is('api/pinpost/upload_image/*'))
            return 'images';
        else if($request->is('api/comment/upload_image/*'))
            return 'images';
        else if($request->is('api/profile/profile_picture/*'))
            return 'profile_pictures';
    }

    /**
     * Check if the imageable object exists
     * @param $imageableType
     * @param $imageableId
     * @return bool
     */
    public function imageableExists($imageableType, $imageableId)
    {
        if($imageableType == 'App\Pinpost') {
            if($this->pinpostRepo->findBy('id', $imageableId))
                return true;
        }
        else if($imageableType == 'App\Comment') {
            if($this->commentRepo->findBy('id', $imageableId))
                return true;
        }
        else if($imageableType == 'App\Profile') {
            if($this->userRepo->findBy('id', $imageableId))
                return true;
        }
        return false;
    }

}