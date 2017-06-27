<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Image;
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

        //use images disk
        Storage::disk('images');

        //perform the json request
        $response = $this->json('POST', '/api/pinpost', [
            'title' => 'testpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail' => UploadedFile::fake()->image('pinimage.jpg')
        ],
            ['Authorization' => $api_token]);

        //get the id of the newly created post
        $id = $response->decodeResponseJson()['thumbnail_id'];

        //check if the id exists
        if($this->assertDatabaseHas('images', ['id' => $id]))
            Storage::disk('images')->assertExists(basename(Image::find($id)->filename));

        $this->assertDatabaseHas('pinposts', ['title' => 'testpin', 'latitude' => 33.33, 'longitude' => 69.69]);
    }

    public function testPinpostRead()
    {

        //create a pinpost, with the title 'pintoupdate' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintoread']);

        $response = $this->json('GET', '/api/pinpost/' . $pinpost->id, [], ['Authorization' => 'token']);
        $response = $response->decodeResponseJson();

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('title', $response);
        $this->assertArrayHasKey('description', $response);
        $this->assertArrayHasKey('latitude', $response);
        $this->assertArrayHasKey('longitude', $response);

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
        ]);

        Storage::disk('images')->assertMissing(basename($filename));

        //check if pin title has been changed
        $this->assertDatabaseHas('pinposts', [
            'title' => 'updatedpin',
            'latitude' => 33.33,
            'longitude' => 69.69
        ]);

    }

    public function testPinpostDelete()
    {

        //create a pinpost, with the title 'pintodelete' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintodelete']);

        $this->assertDatabaseHas('pinposts', ['title' => 'pintodelete']);

        $this->json('DELETE', '/api/pinpost/' . $pinpost->id);

        $this->assertDatabaseMissing('pinposts', ['title' => 'pintodelete']);

    }


}
