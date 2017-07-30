<?php


namespace Tests\Feature;


use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use DatabaseTransactions;

    public function testReplyCreate()
    {
        $user = factory('App\User')->create();
        $comment = factory('App\Comment')->create();
        $response = $this->json('POST', '/api/reply/create/'
        . $comment->id, ['text' => 'test reply'], [
            'Authorization' => 'bearer ' . $user->api_token
        ]);

        $response
            ->assertJson([
            'success' => true,
            'data' => [
                'reply' => [
                    'text' => 'test reply',
                    'comment_id' => $comment->id
                ]
            ]
        ])
            ->assertStatus(200);
    }

    public function testReplyRead()
    {
        $reply = factory('App\Reply')->create();

        $response = $this->json('GET', '/api/reply/read/'
        . $reply->id, []);

        $response
            ->assertJsonStructure([
                'success',
                'data' => [
                    'reply' => [
                        'text',
                        'comment_id'
                    ]
                ]
            ])
            ->assertStatus(200);
    }

    public function testReplyUpdate()
    {
        $reply = factory('App\Reply')->create(['text' => 'test reply']);
        $user = User::find($reply->user_id);

        $response = $this->json('POST', '/api/reply/update/'
        . $reply->id, ['text' => 'updated reply'],
            ['Authorization' => 'bearer ' . $user->api_token]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'reply' => [
                        'text' => 'updated reply',
                        'comment_id' => $reply->comment_id
                    ]
                ]
            ]);

        // Test to see if other users can edit the reply
        $user = factory('App\User')->create();
        $response = $this->json('POST', '/api/reply/update/'
            . $reply->id, ['text' => 'updated reply'],
            ['Authorization' => 'bearer ' . $user->api_token]);
        $response
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'InvalidTokenException'
                ]
            ]);
    }

    public function testReplyDelete()
    {
        $reply = factory('App\Reply')->create(['text' => 'HELLO']);
        $user = User::find($reply->user_id);

        $this->json('DELETE', '/api/reply/delete/'
        . $reply->id, [], ['Authorization' => 'bearer '.$user->api_token]);

        $this->assertDatabaseMissing('replies', [
            'text' => 'HELLO'
        ]);

        // Test to see if other users can delete the reply
        $reply = factory('App\Reply')->create(['text' => 'test reply']);
        $user = factory('App\User')->create();
        $response = $this->json('DELETE', '/api/reply/delete/'
            . $reply->id, [], ['Authorization' => 'bearer '.$user->api_token]);

        $response
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'InvalidTokenException'
                ]
            ]);
        $this->assertDatabaseHas('replies', ['text' => 'test reply']);
    }



}