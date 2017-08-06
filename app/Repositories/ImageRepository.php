<?php

namespace App\Repositories;


use App\Exceptions\Exceptions;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImageRepository
 * @package App\Repositories
 */
class ImageRepository extends Repository
{
    /**
     * @return string
     */
    function model()
    {
        return 'App\Image';
    }

    /**
     * Create an image and store it into storage
     * @param $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function create($request)
    {
        $directory = $request->get('directory');
        $imageableId = $request->get('imageable_id');
        $imageableType = $request->get('imageable_type');
        $userId = $request->get('user_id');



        if($request->has('image_file')) {
            $imageFile = $request->get('image_file');
            if($directory)
                $filename = ImageController::storeImageByUploadedFile($imageFile,
                    $directory);
            else
                $filename = ImageController::storeImageByUploadedFile
                ($imageFile);
        }
        else {
            $url = $request->get('image_url');
            if($directory)
                $filename = ImageController::storeImageByUrl($url, $directory);
            else
                $filename = ImageController::storeImageByUrl($url);
        }

        $image = $this->model->create([
            'user_id' => $userId,
            'filename' => $filename,
            'imageable_type' => $imageableType,
            'imageable_id' => $imageableId,
            'directory' => $directory
        ]);
        return $image;
    }

}