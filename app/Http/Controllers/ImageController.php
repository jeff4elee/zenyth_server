<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as InterventionImage;
use App\Image;

class ImageController extends Controller {

    static public function storeImage(UploadedFile $file, Image $image)
    {

        $extension = $file->extension();

        do {

            $filename = str_random(32) . "." . $extension;
            // Checks if filename is already taken
            $dup_filename = Image::where('filename', $filename)->first();

        } while($dup_filename != null);

        Storage::disk('images')->put($filename, File::get($file));
        $image->filename = $filename;

    }

    public function showImage($filename)
    {
        return InterventionImage::make(storage_path('app/images/' . $filename))
            ->response();
    }

}