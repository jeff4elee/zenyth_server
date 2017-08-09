<?php

Route::post('password/reset/{token}',
    'Auth\ForgotPasswordController@restorePassword')->name('api_pw_reset');


Route::get('email_taken/{email}', 'Auth\RegisterController@emailTaken');
Route::get('username_taken/{username}', 'Auth\RegisterController@usernameTaken');

Route::get('relationship/is_friend/{user1_id}/{user2_id}',
    'RelationshipController@isFriend');
Route::get('picture/{image_id}', 'ImageController@showImage');
Route::get('/profile/profile_picture/{user_id}', 'ProfileController@showProfileImage');

Route::group(['middleware' => ['caching']], function() {

});

Route::group(['middleware' => 'validation'], function() {
    Route::group(['middleware' => 'authenticated'], function() {
        Route::get('user/get_friends/{user_id}', 'UserController@getFriends');
        Route::get('user/blocked_users', 'UserController@blockedUsers');
        Route::get('user/friend_requests', 'UserController@getFriendRequests');


        Route::get('profile/{user_id}', 'ProfileController@read');
        Route::patch('profile', 'ProfileController@update');
        Route::post('profile/profile_picture/upload', 'ProfileController@updateProfilePicture');

        Route::post('relationship/friend_request',
            'RelationshipController@friendRequest');
        Route::post('relationship/response/{requester_id}',
            'RelationshipController@respondToRequest');
        Route::delete('relationship/delete/{user_id}',
            'RelationshipController@deleteFriend');
        Route::get('relationship/block/{user_id}',
            'RelationshipController@blockUser');


        Route::post('pinpost', 'PinpostController@create');
        Route::patch('pinpost/{pinpost_id}', 'PinpostController@update');
        Route::delete('pinpost/{pinpost_id}', 'PinpostController@delete');
        Route::get('pinpost/get_comments/{pinpost_id}', 'PinpostController@fetchComments');
        Route::get('pinpost/get_likes/{pinpost_id}', 'PinpostController@fetchLikes');
        Route::get('pinpost/fetch', 'PinpostController@fetch');
        Route::get('pinpost/images/{pinpost_id}', 'PinpostController@readImages');

        Route::post('pinpost/upload_image/{imageable_id}', 'ImageController@uploadImage');
        Route::post('pinpost/like/{likeable_id}', 'LikeController@create');
        Route::post('pinpost/comment/{commentable_id}', 'CommentController@create');


        Route::post('comment/like/{likeable_id}', 'LikeController@create');
        Route::post('comment/upload_image/{imageable_id}', 'ImageController@uploadImage');
        Route::patch('comment/{comment_id}', 'CommentController@update');
        Route::delete('comment/{comment_id}', 'CommentController@delete');
        Route::get('comment/get_likes/{comment_id}', 'CommentController@fetchLikes');
        Route::get('comment/images/{comment_id}', 'CommentController@readImages');


        Route::post('reply/like/{likeable_id}', 'LikeController@create');
        Route::post('reply/upload_image/{reply_id}', 'ImageController@uploadImage');
        Route::post('reply/{comment_id}', 'ReplyController@create');
        Route::patch('reply/{reply_id}', 'ReplyController@update');
        Route::delete('reply/{reply_id}', 'ReplyController@delete');
        Route::get('reply/get_likes/{reply_id}', 'ReplyController@fetchLikes');
        Route::get('reply/images/{reply_id}', 'ReplyController@readImages');


        Route::delete('like/{like_id}', 'LikeController@delete');

        Route::delete('image/delete/{image_id}', 'ImageController@deleteImage');

        Route::get('tag/search', 'TagController@searchTags');
        Route::get('tag/info', 'TagController@getTagInfo');
    });

    Route::group(['middleware' => 'oauth'], function() {
        Route::post('oauth/register', 'Auth\RegisterController@register');
        Route::post('oauth/login', 'Auth\OauthController@oauthLogin');
    });

    Route::post('register', 'Auth\RegisterController@register');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('password/send_reset_password',
        'Auth\ForgotPasswordController@sendResetPasswordEmail');

    Route::get('user/search_user', 'UserController@searchUser');

});

Route::get('like/read/{like_id}', 'LikeController@read');
Route::get('comment/read/{comment_id}', 'CommentController@read');
Route::get('pinpost/read/{pinpost_id}', 'PinpostController@read');
Route::get('reply/read/{reply_id}', 'ReplyController@read');