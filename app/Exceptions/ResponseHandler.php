<?php

namespace App\Exceptions;

use Illuminate\Validation\Validator;
use Intervention\Image\Facades\Image as InterventionImage;

class ResponseHandler
{
    protected $responseArr;

    public function __construct()
    {
        $this->responseArr = [];
    }

    public function merge($key, $value)
    {
        $responseArr[$key] = $value;
    }

    static public function formatErrors(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $message = implode("\n", $errors);

        return $message;
    }

    static public function errorResponse(\Exception $exception)
    {
        // Prioritize the passed in message if there is one
        // If there is none, use the exception's message
        if($exception->getMessage() != "")
            return response(json_encode([
                'success' => false,
                'error' => [
                    'type' => (string)$exception,
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]
            ]), 200);
        else
            return response(json_encode([
                'success' => false,
                'error' => [
                    'type' => (string)$exception,
                    'code' => $exception->getCode(),
                ]
            ]), 200);
    }

    static public function dataResponse($success, $data, $message = null)
    {
        if($message != null) {
            return response(json_encode([
                'success' => $success,
                'data' => $data,
                'message' => $message
            ]), 200);
        }

        return response(json_encode([
            'success' => $success,
            'data' => $data
        ]), 200);
    }

    static public function successResponse($message = null)
    {
        if($message == null)
            return response(json_encode(['success' => true]), 200);
        else
            return response(json_encode([
                'success' => true,
                'message' => $message
            ]), 200);
    }

    static public function rawImageResponse($path)
    {
        return InterventionImage::make(storage_path($path))
            ->response();
    }

}