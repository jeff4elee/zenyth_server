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

        $user_one = factory('App\User')->create();
        $user_two = factory('App\User')->create();

        $response = $this->json('POST', '/api/relationship/friend_request', [
            'requestee' => $user_two->id
        ],
            ['Authorization' => $user_one->api_token]);

        $response->assertStatus(200);

    }

    public function testFriendAccept()
    {

        $relationship = factory('App\Relationship')->create();

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
                                                         'requestee' => $relationship->requestee,
                                                         'status' => false,
                                                         'blocked' => false]);

        $response = $this->json('POST', '/api/relationship/' . User::find($relationship->requester) . '/response', ['status'=>true],
            ['Authorization' => User::find($relationship->requestee)->api_token]);

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

        $response = $this->json('POST', '/api/relationship/' . $relationship->requester . '/response', ['status'=>false],
            ['Authorization' => User::find($relationship->requestee)->api_token]);

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

        $this->json('DELETE', '/api/relationship/' . $relationship->requester . '/delete', [],
            ['Authorization' => User::find($relationship->requestee)->api_token]);

        $this->assertDatabaseMissing('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee]);

        $relationship = factory('App\Relationship')->create(['status' => true]);

        $this->json('DELETE', '/api/relationship/' . $relationship->requestee . '/delete', [],
            ['Authorization' => User::find($relationship->requester)->api_token]);

        $this->assertDatabaseMissing('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee]);

    }

    public function testFriendBlock()
    {

        $relationship = factory('App\Relationship')->create(['status' => true]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee,
            'status' => true]);

        $this->json('GET', '/api/relationship/' . $relationship->requestee . '/block', [],
            ['Authorization' => User::find($relationship->requester)->api_token]);

        $this->assertDatabaseHas('relationships', ['requester' => $relationship->requester,
            'requestee' => $relationship->requestee, 'blocked' => true]);

    }
    

}
