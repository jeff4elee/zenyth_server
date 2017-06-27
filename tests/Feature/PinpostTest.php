<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Testing;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
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
        Storage::disk('images');

        $this->json('POST', '/api/pinpost', [
            'title' => 'updatedpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail' => UploadedFile::fake()->image('pinimage.png')
        ]);

        Storage::disk('images')->assertExists('pinimage.jpg');
        $this->assertDatabaseHas('pinposts', ['title' => 'testpin', 'latitude' => 42.420, 'longitude' => 66.6]);
    }

    public function testPinpostRead()
    {
        //create a pinpost, with the title 'pintoupdate' and no image
        $pinpost = factory('App\Pinpost')->create(['title' => 'pintoread']);

        $response = $this->json('GET', '/api/pinpost/' . $pinpost->id );
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

        //post request to update the created pin with new values
        $this->json('POST', '/api/pinpost/' . $pinpost->id, [
            'title' => 'updatedpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'thumbnail' => UploadedFile::fake()->image('pinimage.png')
        ]);

        //check the disk if the image has been saved
        Storage::disk('images')->assertExists('pinimage.jpg');

        //update once more, this time replacing the image
        $this->json('POST', '/api/pinpost' . $pinpost->id,
            ['image' => UploadedFile::fake()->image('newimage.jpg')]);

        //check for the new image, and check if the old one is removed
        Storage::disk('images')->assertExists('newimage.jpg');
        Storage::disk('images')->assertMissing('pinimage.jpg');

        //check if pin title has been changed
        $this->assertDatabaseHas('pinposts', ['title' => 'updatedpin']);
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
