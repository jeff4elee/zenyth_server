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
    Route::get('entity/{entity_id}/likesCount',
                'EntityController@likesCount');
    Route::get('entity/{entity_id}/commentsCount',
                'EntityController@commentsCount');
    Route::get('entity/{entity_id}/likes_from_users',
                'EntityController@likesUsers');
    Route::get('entity/{entity_id}/comments',
                'EntityController@comments');

    Route::post('pinpost', 'PinpostController@create');
    Route::get('pinpost/{pinpost_id}', 'PinpostController@read');
    Route::post('pinpost/{pinpost_id}', 'PinpostController@update');
    Route::delete('pinpost/{pinpost_id}', 'PinpostController@delete');

    Route::post('pinvite', 'PinviteController@create');
    Route::get('pinvite/{pinvite_id}', 'PinviteController@read');
    Route::post('pinvite/{pinvite_id}', 'PinviteController@update');
    Route::delete('pinvite/{pinvite_id}', 'PinviteController@delete');

    Route::post('comment', 'CommentController@create');
    Route::get('comment/{comment_id}', 'CommentController@read');
    Route::post('comment/{comment_id}', 'CommentController@update');
    Route::delete('comment/{comment_id}', 'CommentController@delete');

    Route::post('like', 'LikeController@create');
    Route::delete('like/{like_id}', 'LikeController@delete');
});
