<?php

namespace App\Http\Middleware;

use App\Exceptions\ResponseHandler as Response;
use Closure;
use App\Http\Requests\DataValidator;
use App;
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
            return Response::validatorErrorResponse($validator);
        }
        else
            return $next($request);

    }
}