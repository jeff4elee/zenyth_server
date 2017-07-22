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

    static public function formatErrors(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $message = implode("\n", $errors);
        
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
            ]), 200);
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
        ]), 200);
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
            ]), 200);
        } else
            return response(json_encode(['success' => false]), 400);
    }

    static public function dataResponse($data, $message = null)
    {
        if($message != null) {
            return response(json_encode([
                'success' => true,
                'data' => $data,
                'message' => $message
            ]), 200);
        }

        return response(json_encode([
            'success' => true,
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