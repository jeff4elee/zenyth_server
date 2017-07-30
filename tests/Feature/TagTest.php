<?php


namespace Tests\Feature;


use Tests\TestCase;

class TagTest extends TestCase
{
    public function testSearchTagsName()
    {
        $api_token = factory('App\User')->create()->api_token;
        $this->json('POST', '/api/pinpost/create', [
            'title' => 'testpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'tags' => 'hello,hi,howareyou,imokay,disneyland'
        ], ['Authorization' => 'bearer ' . $api_token]);

        $response = $this->json('GET', '/api/tag/search?tag=howareyou');
        $response->assertJson([
            'success' => true,
            'data' => [
                'tags' => [
                    ['name' => 'howareyou']
                ]
            ]
        ]);
    }

    public function testSearchTagInfo()
    {
        $api_token = factory('App\User')->create()->api_token;
        $this->json('POST', '/api/pinpost/create', [
            'title' => 'testpin',
            'description' => 'fake description for fake pins',
            'latitude' => 33.33,
            'longitude' => 69.69,
            'tags' => 'hello,hi,howareyou,imokay,disneyland'
        ], ['Authorization' => 'bearer ' . $api_token]);

        $response = $this->json('GET', '/api/tag/info?tag=howareyou');
        $response->assertJson([
            'success' => true,
            'data' => [
                'pinposts' => [
                    ['title' => 'testpin']
                ]
            ]
        ]);
    }
}