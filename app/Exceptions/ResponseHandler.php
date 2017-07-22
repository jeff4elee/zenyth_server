<?php

namespace App\Exceptions;

use App\Exceptions\Exception;
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

    static public function formatErrors($validator)
    {
        $errors = $validator->errors()->all();
        $message = "";
        foreach($errors as $error) {
            $message = $message . "\n" . $error;
        }
        return $message;
    }

    static public function errorResponse(Exception $exception, $message = null)
    {
        // Prioritize the passed in message if there is one
        // If there is none, use the exception's message
        if($message != null)
        {
            return response(json_encode([
                'success' => false,
                'error' => [
                    'type' => $exception->type,
                    'code' => $exception->statusCode,
                    'message' => $message
                ]
            ]), $exception->statusCode);
        }

        $error = [
            'type' => $exception->type,
            'code' => $exception->statusCode
        ];

        // If there is an exception message, add it to the error array
        if($exception->message != null)
            $error['message'] = $exception->message;

        return response(json_encode([
            'success' => false,
            'error' => $error
        ]), $exception->statusCode);
    }

    static public function validatorErrorResponse(Validator $validator)
    {
        if($validator != null) {
            $message = ResponseHandler::formatErrors($validator);
            $exception = Exceptions::parameterException();

            return response(json_encode([
                'success' => false,
                'error' => [
                    'type' => $exception->type,
                    'code' => $exception->statusCode,
                    'message' => $message
                ]
            ]), $exception->statusCode);
        }
    }

    static public function dataResponse($data, $message = null)
    {
        if($message != null) {
            return response(json_encode([
                'success' => true,
                'data' => $data,
                'message' => $message
            ]));
        }

        return response(json_encode([
            'success' => true,
            'data' => $data
        ]));
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