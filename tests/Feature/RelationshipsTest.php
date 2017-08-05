<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class RelationshipsTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFriendRequest()
    {

        $profile_one = factory('App\Profile')->create();
        $profile_two = factory('App\Profile')->create();

        $user_one = User::find($profile_one->user_id);
        $user_two = User::find($profile_two->user_id);

        $response = $this->json('POST', '/api/relationship/friend_request', [
            'requestee_id' => $user_two->id
        ],
            ['Authorization' => 'bearer ' . $user_one->api_token]);

        $response->assertStatus(200);

    }

    public function testFriendAccept()
    {

        $relationship = factory('App\Relationship')->create();

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
                                                         'requestee' => $relationship->requestee,
                                                         'status' => false,
                                                         'blocked' => false]);

        $response = $this->json('POST', '/api/relationship/response/' .
            $relationship->requester, ['status'=>true],
            ['Authorization' => 'bearer ' .
                User::find($relationship->requestee)->api_token]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
                                                         'requestee' => $relationship->requestee,
                                                         'status' => true,
                                                         'blocked' => false]);

        $response->assertStatus(200);

    }

    public function testFriendReject()
    {

        $relationship = factory('App\Relationship')->create();

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee,
            'status' => false, 'blocked' => false]);

        $response = $this->json('POST', '/api/relationship/response/' .
            $relationship->requester, ['status'=>false],
            ['Authorization' => 'bearer ' .
                User::find($relationship->requestee)->api_token]);

        $this->assertDatabaseMissing('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee]);

        $response->assertStatus(200);

    }

    public function testFriendDelete()
    {

        $relationship = factory('App\Relationship')->create(['status' => true]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee,
            'status' => true]);

        $this->json('DELETE', '/api/relationship/delete/' . $relationship->requester, [],
            ['Authorization' => 'bearer ' . User::find($relationship->requestee)->api_token]);

        $this->assertDatabaseMissing('relationships', [
            'requester' => $relationship->requester,
            'requestee' => $relationship->requestee
        ]);

        $relationship = factory('App\Relationship')->create(['status' => true]);

        $this->json('DELETE', '/api/relationship/delete/' . $relationship->requestee, [],
            ['Authorization' => 'bearer ' . User::find($relationship->requester)->api_token]);

        $this->assertDatabaseMissing('relationships', [
            'requester' => $relationship->requester,
            'requestee' => $relationship->requestee
        ]);

    }

    public function testFriendBlock()
    {

        $relationship = factory('App\Relationship')->create(['status' => true]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee,
            'status' => true]);

        $this->json('GET', '/api/relationship/block/' . $relationship->requestee, [],
            ['Authorization' => 'bearer ' . User::find($relationship->requester)->api_token]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee, 'status' => false, 'blocked' => true]);

    }

    public function testIsFriend()
    {
        $relationship = factory('App\Relationship')->create(['status' => true]);

        $response = $this->json('GET', '/api/relationship/is_friend/'
        . $relationship->requester . '/' . $relationship->requestee);

        $response->assertJson([
            'success' => true,
            'data' => [
                'is_friend' => true
            ]
        ]);

        $user = factory('App\User')->create();
        $response = $this->json('GET', '/api/relationship/is_friend/'
            . $relationship->requester . '/' . $user->id);

        $response->assertJson([
            'success' => true,
            'data' => [
                'is_friend' => false
            ]
        ]);
    }

}
