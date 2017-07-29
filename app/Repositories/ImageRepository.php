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

        $image = $this->model->create([]);

        if($request->has('image_file')) {
            $imageFile = $request->get('image_file');
            if($directory)
                $filename = ImageController::storeImageByUploadedFile($imageFile,
                    $image, $directory);
            else
                $filename = ImageController::storeImageByUploadedFile
                ($imageFile, $image);
        }
        else {
            $url = $request->get('image_url');
            if($directory)
                $filename = ImageController::storeImageByUrl($url,
                    $image, $directory);
            else
                $filename = ImageController::storeImageByUrl($url, $image);
        }

        $image->update([
            'user_id' => $userId,
            'filename' => $filename,
            'imageable_type' => $imageableType,
            'imageable_id' => $imageableId,
            'directory' => $directory
        ]);
        return $image;
    }

    /**
     * Delete an image
     * @param $request
     * @param $id
     * @return mixed
     */
    public function delete($request, $id)
    {
        $image = $this->model->where('id', '=', $id)->first();
        $userId = $request->get('user_id');

        if(!$image)
            Exceptions::notFoundException(NOT_FOUND);

        if($image->user_id != $userId)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $directory = $image->directory;
        Storage::disk($directory)->delete($image->filename);
        return $image->delete();
    }
}