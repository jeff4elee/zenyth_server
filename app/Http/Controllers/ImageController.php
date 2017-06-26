<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as InterventionImage;
use App\Image;

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
    static public function storeImage(UploadedFile $file, Image $image)
    {

        $extension = $file->extension();

        do {

            $filename = str_random(32) . "." . $extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while ($dup_filename != null);

        Storage::disk('images')->put($filename, File::get($file));
        $image->filename = $filename;

    }

    /**
     * Shows the Image
     *
     * @param $filename, name of image file
     * @return mixed, an image response
     */
    public function showImage($filename)
    {
        return InterventionImage::make(storage_path('app/images/' . $filename))
            ->response();
    }

}