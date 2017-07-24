<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('new-post', 'PostController@create');

Route::post('new-post', 'PostController@store');

Route::get('/home', 'PostController@index');

Auth::routes();

Route::get('register/verify/{confirmation_code}', [
    'as' => 'confirmation_path',
    'uses' => 'Auth\RegisterController@confirm'
]);

Route::get('password/reset/{token}', [
    'as' => 'password_reset_path',
    'uses' => 'Auth\ResetPasswordController@showPasswordResetBlade'
]);

Route::get('message', function () {
    $app = PHPRedis::connection();
    $app->set("masterpowers", "Yeah Baby Yeah");
    print_r($app->get("masterpowers"));
});