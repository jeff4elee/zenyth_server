<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetFriends()
    {
        $profile = factory('App\Profile')->create();
        $user = User::find($profile->user_id);
        $friendsIdArray = [];
        for($i = 0; $i < 10; $i++) {
            $friendsId = factory('App\Relationship')->create(['requester' =>
                $user->id, 'status' => true])->requestee;
            array_push($friendsIdArray, $friendsId);
        }

        $response = $this->json('GET', '/api/user/get_friends/'
        . $user->id, [], ['Authorization' => 'bearer ' . $user->api_token]);

        $friends = $response->decodeResponseJson()['data']['users'];
        foreach($friends as $friend) {
            $this->assertTrue(isset($friend['id'], $friendsIdArray));
        }

    }


    public function testSearchBlockedUsers()
    {
        $profile = factory('App\Profile')->create();
        $user = User::find($profile->user_id);
        $blockedUsersIdArray = [];
        for($i = 0; $i < 10; $i++) {
            $blockedUserId = factory('App\Relationship')->create(['requester' =>
                $user->id, 'blocked' => true])->requestee;
            array_push($blockedUsersIdArray, $blockedUserId);
        }

        $response = $this->json('GET', '/api/user/blocked_users',
            [], ['Authorization' => 'bearer ' . $user->api_token]);

        $blockedUsers = $response->decodeResponseJson()['data']['users'];

        foreach($blockedUsers as $blockedUser) {
            $this->assertTrue(isset($blockedUser['id'], $blockedUsersIdArray));
        }

    }


    public function testSearchFriendRequests()
    {
        $profile = factory('App\Profile')->create();
        $user = User::find($profile->user_id);
        $friendRequesterIdArray = [];
        for($i = 0; $i < 10; $i++) {
            $friendRequesterId = factory('App\Relationship')->create(['requestee' =>
                $user->id, 'blocked' => false, 'status' => false])->requester;
            array_push($friendRequesterIdArray, $friendRequesterId);
        }

        $response = $this->json('GET', '/api/user/friend_requests',
            [], ['Authorization' => 'bearer ' . $user->api_token]);
        $friendRequesters = $response->decodeResponseJson()['data']['users'];
        foreach($friendRequesters as $friendRequester) {
            $this->assertTrue(isset($friendRequester['id'], $friendRequesterIdArray));
        }
    }


    public function testSearchUsers()
    {
        $this->json('POST', '/api/register', [
            'username' => 'Hoang',
            'gender' => 'non-binary',
            'email' => 'test@email.com',
            'password' => 'password',
            'password_confirmation' => 'password']);

        $response = $this->json('GET', '/api/user/search_user?keyword=Hoang');

        $response->assertJson([
            'success' => true,
            'data' => [
                'users' => [
                    ['username' => 'Hoang']
                ]
            ]
        ]);
    }

}

