<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use App\Image;

class ImageController extends Controller {
    static $IMAGE_PATH = "/storage/app/images/";

    static public function storeImage(UploadedFile $file, Image $image)
    {
        
        $extension = $file->extension();

        do {

            $filename = str_random(45) . "." . $extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while($dup_filename != null);

        Storage::disk('images')->put($filename, File::get($file));
        $image->filename = $filename;
        $image->path = self::$IMAGE_PATH . $filename;

    }

}