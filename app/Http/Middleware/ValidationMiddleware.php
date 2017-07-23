<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\ResponseHandler;
use App\Http\Requests\DataValidator;
use Closure;
use Illuminate\Http\Request;


class ValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $validator = DataValidator::validate($request);
        if($validator != null && $validator->fails()) {
            $message = ResponseHandler::formatErrors($validator);
            throw new App\Exceptions\InvalidParameterException($message, 201);
            //return Response::validatorErrorResponse($validator);
        }
        else
            return $next($request);

    }
}