<?php


namespace Tests\Feature;

use App\Image;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use DatabaseTransactions;

    public function testUploadImage()
    {
        $pinpost = factory('App\Pinpost')->create();
        $response = $this->json('POST', '/api/pinpost/upload_image/'
            . $pinpost->id, ['image' => UploadedFile::fake()->image('pinimage.jpg')],
            ['Authorization' => 'bearer ' . User::find($pinpost->user_id)->api_token]);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'image' => [
                    'filename'
                ]
            ]
        ]);

        $image_id = $response->decodeResponseJson()['data']['image']['id'];

        Storage::disk('images')->assertExists(basename(Image::find($image_id)
            ->filename));

        $user = factory('App\User')->create();
        $response = $this->json('POST', '/api/pinpost/upload_image/'
            . $pinpost->id, ['image' => UploadedFile::fake()->image('pinimage.jpg')],
            ['Authorization' => 'bearer ' . $user->api_token]);
        $response->assertJson([
            'success' => false,
            'error' => [
                'type' => 'InvalidTokenException'
            ]
        ]);
    }
}