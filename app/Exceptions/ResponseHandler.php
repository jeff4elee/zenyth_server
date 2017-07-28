<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Validator;
use Intervention\Image\Facades\Image as InterventionImage;

/**
 * Class ResponseHandler
 * @package App\Exceptions
 */
class ResponseHandler
{
    /**
     * Array containing key values that are part of the body of response
     * @var array
     */
    protected $bodyArr;
    /**
     * Response getting returned
     * @var
     */
    protected $response;
    /**
     * Status code of the response
     * @var
     */
    protected $statusCode;


    /**
     * ResponseHandler constructor.
     * For manually formatting a response
     * @param Response $response
     * @param int $statusCode
     */
    public function __construct($response = null, $statusCode = 200)
    {
        if($response instanceof JsonResponse) {
            $this->bodyArr = $response->getData();
            $this->response = $response;
        }
        else {
            $this->bodyArr = [];
            $this->response = response()->json(array(), $statusCode, array(),
                JSON_PRETTY_PRINT);
        }
        $this->statusCode = $statusCode;
    }

    /**
     * Add keys and values to the body of the response
     * @param $bodyArr
     */
    public function append($bodyArr)
    {
        foreach($bodyArr as $key => $value)
            $this->bodyArr[$key] = $value;
    }

    /**
     * Append to the header of the response
     * @param $headerArr
     */
    public function appendHeader($headerArr)
    {
        $this->response->withHeaders($headerArr);
    }

    /**
     * Append to the 'data' key. If there isn't one, add one
     * @param $key
     * @param $value
     */
    public function appendData($key, $value)
    {
        // If the data key does not exist, add key 'data' with value of empty
        // array
        if(!array_key_exists('data', $this->bodyArr)) {
            array_add($this->bodyArr, 'data', array());
            array_add($this->bodyArr['data'], $key, $value);
        }
        // Else just add to key data
        array_add($this->bodyArr['data'], $key, $value);
    }

    /**
     * Construct and return the response
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function response()
    {
        $this->response->setData($this->bodyArr);
        return $this->response;
    }

    /**
     * Formats the validator's array of error messages into one string
     * @param Validator $validator
     * @return string
     */
    static public function formatErrors(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $message = implode("\n", $errors);

        return $message;
    }

    /**
     * Format a response based on the exception
     * @param \Exception $exception
     * @return JsonResponse
     */
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

    /**
     * Format a response with data
     * @param $success, status of request
     * @param $data, data to be put inside of the response
     * @param null $message, message to be added to the response
     * @return JsonResponse
     */
    static public function dataResponse($success, array $data, $message = null)
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

    /**
     * Format a successful response
     * @param null $message, message to be added to the response
     * @return JsonResponse
     */
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

    /**
     * Raw image response
     * @param $path, path to the image
     * @return mixed
     */
    static public function rawImageResponse($path)
    {
        return InterventionImage::make(storage_path($path))
            ->response();
    }

}