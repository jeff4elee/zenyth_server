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

Route::get('/', function() {
  return view('login');
}) -> name('login');

Route::post('/login', [
    'uses' => 'UserController@postLogin',
    'as' => 'login',
]);

Route::post('/signup', [
    'uses' => 'UserController@postSignUp',
    'as' => 'signup'
]);

Route::get('/logout', [
    'uses' => 'UserController@getLogout',
    'as' => 'signup'
]);

Route::get('new-post', 'PostController@create');

Route::post('new-post', 'PostController@store');

Route::get('/home', 'PostController@index');

Auth::routes();

