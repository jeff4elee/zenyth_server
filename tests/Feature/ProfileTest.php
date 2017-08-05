<?php


namespace Tests\Feature;


use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdateProfile()
    {
        $profile = factory('App\Profile')->create();
        $userPrivacy = factory('App\UserPrivacy')->create(['user_id' =>
            $profile->user_id]);
        $user = User::find($profile->user_id);

        // Updating first name, last name and gender
        $response = $this->json('POST', '/api/profile/update',
            ['first_name' => 'Test', 'last_name' => 'Man',
                'gender' => 'non-binary'],
            ['Authorization' => 'bearer ' . $user->api_token]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'profile' => [
                        'first_name' => 'Test',
                        'last_name' => 'Man',
                        'gender' => 'non-binary'
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => 'Test',
            'last_name' => 'Man'
        ]);

        // Updating privacy settings
        $this->json('POST', '/api/profile/update',
            ['email_privacy' => 'self', 'birthday_privacy' => 'friends'],
            ['Authorization' => 'bearer ' . $user->api_token]);
        $this->assertDatabaseHas('user_privacies', [
            'user_id' => $userPrivacy->user_id,
            'email_privacy' => 'self',
            'birthday_privacy' => 'friends'
        ]);
    }

    public function testReadProfile()
    {
        $profile = factory('App\Profile')->create();
        $user = User::find($profile->user_id);
        $userPrivacy = factory('App\UserPrivacy')->create([
            'user_id' => $profile->user_id,
            'email_privacy' => 'self',
            'birthday_privacy' => 'friends'
        ]);

        // Test reading your own profile
        $response = $this->json('GET', 'api/profile/read/' .
            $profile->user_id, [],
            ['Authorization' => 'bearer ' . $user->api_token]);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'email',
                    'profile' => [
                        'gender',
                        'birthday'
                    ]
                ]
            ]
        ]);

        // Test reading someone else's profile
        $userTwo = factory('App\User')->create();
        $response = $this->json('GET', 'api/profile/read/' .
            $profile->user_id, [],
            ['Authorization' => 'bearer ' . $userTwo->api_token]);

        $response->assertJsonMissing([
            'data' => [
                'user' => [
                    'email' => $user->email,
                    'profile' => [
                        'birthday' => $profile->birthday
                    ]
                ]
            ]
        ]);

        // Test reading someone else's profile if you are friend with that
        // person
        factory('App\Relationship')->create([
            'requester' => $profile->user_id,
            'requestee' => $userTwo->id,
            'status' => true
        ]);
        $response = $this->json('GET', 'api/profile/read/' .
            $profile->user_id, [],
            ['Authorization' => 'bearer ' . $userTwo->api_token]);

        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'profile' => [
                        'birthday'
                    ]
                ]
            ]
        ]);
    }
}