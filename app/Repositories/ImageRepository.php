<?php

namespace App\Repositories;


use App\Exceptions\Exceptions;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function create(Request $request)
    {
        $directory = $request->get('directory');
        if($request->has('image_file')) {
            $imageFile = $request->get('image_file');
            if($directory)
                $filename = ImageController::storeImageByUploadedFile($imageFile,
                    $directory);
            else
                $filename = ImageController::storeImageByUploadedFile($imageFile);
        }
        else {
            $url = $request->get('image_url');
            if($directory)
                $filename = ImageController::storeImageByUrl($url, $directory);
            else
                $filename = ImageController::storeImageByUrl($url);
        }

        $image = $this->model->create(['filename' => $filename]);
        return $image;
    }

    /**
     * Update an image. Delete the old image inside of storage
     * @param Request $request
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(Request $request, $id, $attribute = 'id')
    {
        $image = $this->model->where($attribute, '=', $id)->first();
        $directory = $request->get('directory');
        if(!$image)
            Exceptions::notFoundException(NOT_FOUND);

        $oldFilename = $image->filename;
        Storage::disk($directory)->delete($oldFilename);

        if($request->has('image_file')) {
            $imageFile = $request->get('image_file');
            $filename = ImageController::storeImageByUploadedFile($imageFile,
                $directory);
        }
        else {
            $url = $request->get('image_url');
            $filename = ImageController::storeImageByUrl($url, $directory);
        }
        $image->filename = $filename;
        $image->update();
        return $image;
    }

    /**
     * Delete an image
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function delete(Request $request, $id)
    {
        $image = $this->model->where('id', '=', $id)->first();
        if(!$image)
            Exceptions::notFoundException(NOT_FOUND);

        $directory = $request->get('directory');
        Storage::disk($directory)->delete($image->filename);
        return $image->delete();
    }
}