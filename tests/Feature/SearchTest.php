<?php

namespace Tests\Feature;

use App\Profile;
use App\Relationship;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchTest extends TestCase
{
//Route::get('user/get_blocked_users', 'UserController@blockedUsers');
//Route::get('user/get_friend_requests', 'UserController@getFriendRequests');
//Route::get('user/search_user/{name}', 'UserController@searchUser');

    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->createApplication();

        Artisan::call('migrate:refresh');

        Artisan::call('db:seed', ['--class' => 'UserSearchTableSeeder']);
    }

    public function testSearchBlockedUsers(){

        $user = User::inRandomOrder()->first();

        $blocked_list = $this->get('/api/user/blocked_users', ['Authorization' => $user->api_token])->decodeResponseJson();

        foreach($blocked_list as $blocked_user){

            $relationship = Relationship::where(function ($query) use ($blocked_user, $user) {
                $query->where('requester', $user->id)
                    ->orWhere('requestee', $blocked_user->id);
            })->where(function ($query) use ($blocked_user, $user) {
                $query->where('requestee', $user->id)
                    ->orWhere('requester', $blocked_user->id);
            })->first();

            $this->assertNotNull($relationship);

            if($relationship != null) {
                $this->assertTrue($relationship->blocked);
            }

        }

    }

    public function testSearchFriendRequests(){

        $user = User::inRandomOrder()->first();

        $pending_friends = $this->get('/api/user/friend_requests', ['Authorization' => $user->api_token])->decodeResponseJson();

        foreach($pending_friends as $pending_friend){

            $relationship = Relationship::where(function ($query) use ($pending_friend, $user) {
                $query->where('requester', $user->id)
                    ->orWhere('requestee', $pending_friend->id);
            })->where(function ($query) use ($pending_friend, $user) {
                $query->where('requestee', $user->id)
                    ->orWhere('requester', $pending_friend->id);
            })->first();

            $this->assertNotNull($relationship);

            if($relationship != null) {
                $this->assertFalse($relationship->status);
            }

        }

    }

    public function testSearchUsers(){

        $users = $this->call('GET', '/api/user/search_user/Rob')->decodeResponseJson();

        foreach($users as $user){

            $profile = Profile::where('user_id', $user->id)->first();

            $this->assertNotNull($profile);

            if($profile != null) {
                $this->assertTrue($profile->first_name === 'Rob' || $profile->last_name === 'Rob');
            }

        }

        $users = $this->call('GET', '/api/search_user/Jeff')->decodeResponseJson();

        foreach($users as $user){

            $profile = Profile::where('user_id', $user->id)->first();

            $this->assertNotNull($profile);

            if($profile != null) {
                $this->assertTrue($profile->first_name === 'Jeff');
            }

        }

    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        Artisan::call('migrate:refresh');
    }

}
