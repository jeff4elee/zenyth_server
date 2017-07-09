<?php

namespace Tests\Feature;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Image;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PinpostTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPinpostCreation()
    {
        //create pesudouser
        $api_token = factory('App\User')->create()->api_token;

        //perform the json request
        $response = $this->json('POST', '/api/pinpost', [
            'title' => 'testpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail' => UploadedFile::fake()->image('pinimage.jpg')
        ], ['Authorization' => 'bearer ' . $api_token]);

        //get the id of the newly created post
        $image_id = $response->decodeResponseJson()['data']['thumbnail_id'];

        //check if the id exists
        if($this->assertDatabaseHas('images', ['id' => $image_id]))
            Storage::disk('images')->assertExists(basename(Image::find($image_id)->filename));

        $this->assertDatabaseHas('pinposts', [
            'title' => 'testpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail_id' => $image_id
        ]);

    }

    public function testPinpostRead()
    {

        //create a pinpost, with the title 'pintoread' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintoread']);

        $response = $this->json('GET', '/api/pinpost/' . $pinpost->id, [],
            ['Authorization' => 'bearer ' . 'token']);

        $response->assertJson([
            'success' => true,
            'data' => [
                'entity_id' => $pinpost->entity_id,
                'title' => $pinpost->title,
                'description' => $pinpost->description,
                'latitude' => $pinpost->latitude,
                'longitude' => $pinpost->longitude,
                'thumbnail_id' => $pinpost->thumbnail_id,
                'creator_id' => $pinpost->creator_id
            ]
        ]);

    }

    public function testPinpostUpdate()
    {

        Storage::disk('images');

        //create a pinpost, with the title 'pintoupdate' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintoupdate']);

        $filename = Image::find($pinpost->thumbnail_id)->filename;

        //post request to update the created pin with new values
        $this->json('POST', '/api/pinpost/' . $pinpost->id, [
            'title' => 'updatedpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail' => UploadedFile::fake()->image('pinimage.jpg')
        ], ['Authorization' => 'bearer ' . User::find($pinpost->creator_id)->api_token]);

        //check if pin title has been changed
        $this->assertDatabaseHas('pinposts', [
            'title' => 'updatedpin',
            'latitude' => 33.33,
            'longitude' => 69.69
        ]);

        Storage::disk('images')->assertMissing(basename($filename));

    }

    public function testPinpostDelete()
    {

        //create a pinpost, with the title 'pintodelete' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintodelete']);

        $this->assertDatabaseHas('pinposts', ['title' => 'pintodelete']);

        $this->json('DELETE', '/api/pinpost/' . $pinpost->id, [],
            ['Authorization' => 'bearer ' . User::find($pinpost->creator_id)->api_token]);

        $this->assertDatabaseMissing('pinposts', ['title' => 'pintodelete']);

    }


}
