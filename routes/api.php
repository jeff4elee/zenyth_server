<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LogoutController@logout');

Route::group(['middleware' => 'authenticated'], function() {
    Route::post('pinpost', 'PinpostController@create');
    Route::get('pinpost/{entity_id}', 'PinpostController@read');
    Route::post('pinpost/{entity_id}', 'PinpostController@update');
    Route::delete('pinpost/{entity_id}', 'PinpostController@delete');

    Route::post('pinvite', 'PinviteController@create');
    Route::get('pinvite/{entity_id}', 'PinviteController@read');
    Route::patch('pinvite/{entity_id}', 'PinviteController@update');
    Route::delete('pinvite/{entity_id}', 'PinviteController@delete');

    Route::post('comment', 'CommentController@create');
    Route::get('comment/{comment_id}', 'CommentController@read');
    Route::patch('comment/{comment_id}', 'CommentController@update');
    Route::delete('comment/{comment_id}', 'CommentController@delete');
    Route::get('comment/{comment_id}/count', 'CommentController@count');

    Route::post('like', 'LikeController@create');
    Route::delete('like/{like_id}/count', 'LikeController@delete');
    Route::get('like/{like_id}', 'LikeController@count');
});
