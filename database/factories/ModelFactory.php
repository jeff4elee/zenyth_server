<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Illuminate\Support\Facades\Storage;
use App\User;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    do {
        $username = $faker->userName;
        $user = User::where('username', $username)->first();
    } while(strlen($username) > 20 || !ctype_alnum($username) || $user != null);
    do {
        $email = $faker->safeEmail;
        $user = User::where('email', $email)->first();
    } while($user != null);

    return [
        'username' => $username,
        'email' => $email,
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'api_token' => str_random(60),
    ];

});

$factory->define(App\Profile::class, function (Faker\Generator $faker) {

    $user_id = factory('App\User')->create()->id;

    return [
        'user_id' => $user_id,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'gender' => ((bool) random_int(0, 1)) ? 'male' : 'female'
    ];

});

$factory->define(App\UserPrivacy::class, function (Faker\Generator $faker) {

    $user_id = factory('App\User')->create()->id;

    return [
        'user_id' => $user_id,
    ];

});

$factory->define(App\Pinpost::class, function (Faker\Generator $faker) {

    return [
        'title' => $faker->city,
        'description' => $faker->text(200),
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'user_id' => factory('App\Profile')->create()->user_id
    ];

});

$factory->define(App\Image::class, function (Faker\Generator $faker) {

    Storage::disk('images');

    $filename = $faker->image(public_path().'/../storage/app/images');
    $pinpost = factory('App\Pinpost')->create();

    return [
        'filename' => basename($filename),
        'imageable_type' => 'App\Pinpost',
        'imageable_id' => $pinpost->id,
        'user_id' => $pinpost->user_id,
        'directory' => 'images'
    ];

});

$factory->define(App\Relationship::class, function (Faker\Generator $faker) {

    return [
        'requester' => factory('App\Profile')->create()->user_id,
        'requestee' => factory('App\Profile')->create()->user_id
    ];

});

$factory->define(App\Comment::class, function (Faker\Generator $faker) {

    return [
        'commentable_id' => factory('App\Pinpost')->create()->id,
        'commentable_type' => 'App\Pinpost',
        'user_id' => factory('App\Profile')->create()->user_id,
        'comment' => $faker->text(100)
    ];

});

$factory->define(App\Like::class, function (Faker\Generator $faker) {

    return [
        'likeable_id' => factory('App\Pinpost')->create()->id,
        'likeable_type' => 'App\Pinpost',
        'user_id' => factory('App\Profile')->create()->user_id
    ];

});

$factory->define(App\Reply::class, function (Faker\Generator $faker) {

    $pinpost = factory('App\Pinpost')->create();

    return [
        'comment_id' => factory('App\Comment')->create()->id,
        'user_id' => factory('App\Profile')->create()->user_id,
        'text' => $faker->text
    ];

});