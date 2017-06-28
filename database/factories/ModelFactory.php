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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make($faker->password(6, 10)),
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

$factory->define(App\Entity::class, function (Faker\Generator $faker) {

    return [
    ];

});

$factory->define(App\Pinpost::class, function (Faker\Generator $faker) {

    return [
        'creator_id' => factory('App\User')->create()->id,
        'title' => $faker->city,
        'description' => $faker->text(200),
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'entity_id' => factory('App\Entity')->create()->id,
        'thumbnail_id' => factory('App\Image')->create()->id
    ];

});

$factory->define(App\Image::class, function (Faker\Generator $faker) {

    Storage::disk('images');

    $filename = $faker->image(public_path().'/../storage/app/images');

    return [
        'filename' => basename($filename)
    ];

});

$factory->define(App\Relationship::class, function (Faker\Generator $faker) {

    $user1 = factory('App\User')->create();
    $user2 = factory('App\User')->create();

    return [
        'requester' => $user1->id,
        'requestee' => $user2->id
    ];

});