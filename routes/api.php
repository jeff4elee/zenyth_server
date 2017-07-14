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
Route::post('oauth/register', 'Auth\RegisterController@oauthRegister');
Route::get('email_taken/{email}', 'Auth\RegisterController@emailTaken');
Route::get('username_taken/{username}', 'Auth\RegisterController@usernameTaken');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LogoutController@logout');
Route::post('password/send_reset_password',
            'Auth\ForgotPasswordController@sendResetPasswordEmail');
Route::post('password/reset/{token}',
            'Auth\ResetPasswordController@restorePassword')->name('api_pw_reset');
Route::post('oauth/login', 'Auth\OauthController@oauthLogin');

Route::get('user/search_user/{name}', 'UserController@searchUser');
Route::get('comment/read/{comment_id}', 'CommentController@read');
Route::get('pinpost/read/{pinpost_id}', 'PinpostController@read');
Route::get('pinvite/read/{pinvite_id}', 'PinviteController@read');

Route::group(['middleware' => 'authenticated'], function() {

    Route::get('user/get_friends/{user_id}', 'UserController@getFriends');
    Route::get('user/blocked_users', 'UserController@blockedUsers');
    Route::get('user/friend_requests', 'UserController@getFriendRequests');

    Route::post('profile/update', 'ProfileController@update');

    Route::post('relationship/friend_request',
                'RelationshipController@friendRequest');
    Route::post('relationship/response/{requester_id}',
                'RelationshipController@respondToRequest');
    Route::delete('relationship/delete/{user_id}',
                'RelationshipController@deleteFriend');
    Route::get('relationship/block/{user_id}',
                'RelationshipController@blockUser');
    Route::get('relationship/friended/{user1_id}/{user2_id}',
                'RelationshipController@friended');

    Route::get('entity/likes_count/{entity_id}',
                'EntityController@likesCount');
    Route::get('entity/commentsCount/comments_count',
                'EntityController@commentsCount');
    Route::get('entity/likes_from_users/{entity_id}',
                'EntityController@likesUsers');
    Route::get('entity/comments/{entity_id}',
                'EntityController@comments');

    Route::post('pinpost/create', 'PinpostController@create');
    Route::post('pinpost/update/{pinpost_id}', 'PinpostController@update');
    Route::delete('pinpost/delete/{pinpost_id}', 'PinpostController@delete');


    Route::post('pinvite/create', 'PinviteController@create');
    Route::post('pinvite/update/{pinvite_id}', 'PinviteController@update');
    Route::delete('pinvite/delete/{pinvite_id}', 'PinviteController@delete');
    Route::post('pinvite/uploadPicture/{pinvite_id}',
                    'PinviteController@uploadPicture');
    Route::delete('pinvite/deletePicture/{image_id}',
                    'PinviteController@deletePicture');

    Route::post('comment/create', 'CommentController@create');
    Route::post('comment/update/{comment_id}', 'CommentController@update');
    Route::delete('comment/delete/{comment_id}', 'CommentController@delete');

    Route::post('like/create', 'LikeController@create');
    Route::delete('like/delete/{entity_id}', 'LikeController@delete');

    Route::get('storage/{filename}', 'ImageController@showImage');

});

