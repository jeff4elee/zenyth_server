<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Custom validation rule for validating geographic coordinates
        Validator::extend('valid_coord', function($attribute, $value,
                                                  $parameters, $validator) {
            if(empty($value))
                return false;

            $coord = explode(",", $value);
            if(count($coord) != 2)
                return false;

            $lat = $coord[0];
            $long = $coord[1];

            // The lat and long must be numeric
            if(!is_numeric($lat) || !is_numeric($long))
                return false;

            // Latitude must be between -90 and 90
            if(!($lat >= -90) || !($lat <= 90))
                return false;

            // Longitude must be between -180 and 180
            if(!($long >= -180) || !($long <= 180))
                return false;

            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
