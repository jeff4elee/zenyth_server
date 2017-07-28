<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Image;
use App\Repositories\ImageRepository;
use GuzzleHttp\Client;
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
    static public function storeImageByUploadedFile(UploadedFile $file,
                                                    $directory = 'images')
    {
        if($directory == null)
            $directory = 'images';

        $extension = $file->extension();
        $filename = ImageController::generateImagename($extension);

        Storage::disk($directory)->put($filename, File::get($file));
        return $filename;
    }

    /**
     * Store a profile image into storage
     * @param $url
     * @param $directory
     * @return Image|mixed|null|\Psr\Http\Message\ResponseInterface
     */
    static public function storeImageByUrl($url, $directory = 'images')
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
            $image = $client->request('GET', $url);
            $contentType = strtolower($image->getHeader('Content-Type')[0]);
            $extension = $mimeTypes[$contentType];

            if($extension == null)
                Exceptions::invalidImageTypeException(INVALID_IMAGE_TYPE);

            $filename = ImageController::generateImageName($extension);
            Storage::disk($directory)->put($filename, $image->getBody());

            return $filename;
        } catch (\Exception $error) {
            // Log the error or something
            Log::info($error);
            return null;
        }
    }

    /**
     * Show the Image
     * @param $filename, name of image file
     * @return mixed, an image response
     */
    public function showImage($image_id)
    {
        $image = $this->imageRepo->read($image_id);
        if($image == null)
            Exceptions::notFoundException('Image not found');

        $path = 'app/images/' . $image->filename;
        return Response::rawImageResponse($path);
    }

    /**
     * Generate a unique image name
     * @param $extension
     * @return string
     */
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