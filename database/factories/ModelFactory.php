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
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'api_token' => str_random(60),
    ];
});

$factory->define(App\Entity::class, function (Faker\Generator $faker) {

    return [
    ];

});

$factory->define(App\Pinpost::class, function (Faker\Generator $faker) {

    return [
        'user_id' => factory('App\User')->create()->id,
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

    $filename = str_random(45) . '.jpg';

    $faker->image(Storage::url('app/images/' . $filename));

    return [
        'filename' => $filename
    ];

});