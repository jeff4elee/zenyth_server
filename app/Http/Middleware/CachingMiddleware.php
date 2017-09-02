<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CachingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $minutes = 1)
    {

        $url = request()->url();

        $queryParams = request()->query();

        //Sorting query params by key (acts by reference)
        ksort($queryParams);

        //Transforming the query array to query string
        $queryString = http_build_query($queryParams);

        $key = "{$url}?{$queryString}";

        if (Cache::has($key)) {
            return Cache::get($key);
        } else {
            $response = $next($request);
            Cache::put($key, $response, $minutes);
            return $response;
        }

    }

}
