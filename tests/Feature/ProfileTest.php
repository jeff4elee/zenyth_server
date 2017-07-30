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
        $user = User::find($profile->user_id);
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
    }
}