<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\Exceptions;
use App\Exceptions\InvalidParameterException;
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
            Exceptions::parameterException($message);
        }
        else
            return $next($request);

    }
}