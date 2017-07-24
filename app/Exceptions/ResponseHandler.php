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
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $exception->getMessage(),
                    'type' => (string)$exception,
                    'code' => $exception->getCode()
                ]
            ], 200, array(), JSON_PRETTY_PRINT)
                ->header('Content-Type', 'application/json; charset=UTF-8');
        else
            return response()->json([
                'success' => false,
                'error' => [
                    'type' => (string)$exception,
                    'code' => $exception->getCode(),
                ]
            ], 200, array(), JSON_PRETTY_PRINT)
                ->header('Content-Type', 'application/json; charset=UTF-8');
    }

    static public function dataResponse($success, $data, $message = null)
    {
        if($message != null) {
            return response()->json([
                'success' => $success,
                'data' => $data,
                'message' => $message
            ], 200, array(), JSON_PRETTY_PRINT)
                ->header('Content-Type', 'application/json; charset=UTF-8');
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ], 200, array(), JSON_PRETTY_PRINT)
            ->header('Content-Type', 'application/json; charset=UTF-8');
    }

    static public function successResponse($message = null)
    {
        if($message == null)
            return response()->json(['success' => true], 200, array(), JSON_PRETTY_PRINT)
                ->header('Content-Type', 'application/json; charset=UTF-8');
        else
            return response()->json([
                'success' => true,
                'message' => $message
            ], 200, array(), JSON_PRETTY_PRINT)
                ->header('Content-Type', 'application/json; charset=UTF-8');
    }

    static public function rawImageResponse($path)
    {
        return InterventionImage::make(storage_path($path))
            ->response();
    }

}