<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchTest extends TestCase
{
//Route::get('user/get_blocked_users', 'UserController@blockedUsers');
//Route::get('user/get_friend_requests', 'UserController@getFriendRequests');
//Route::get('user/search_user/{name}', 'UserController@searchUser');
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testSearchBlockedUser(){


        $this->get('/api/user/get_blocked_users', ['Authorization'])
    }

}
