<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


class AuthTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testWrongMethodRequests()
    {

        $response = $this->get('/api/register');
        $response->assertStatus(405);

        $response = $this->get('/api/login');
        $response->assertStatus(405);

        $response = $this->delete('/api/register');
        $response->assertStatus(405);

        $response = $this->delete('/api/login');
        $response->assertStatus(405);

    }

    public function testRegistration()
    {

        $response = $this->json('POST', '/api/register', [
            'username' => 'testman',
            'gender' => 'non-binary',
            'email' => 'test@email.com',
            'password' => 'password',
            'password_confirmation' => 'password']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'test@email.com',
                        'username' => 'testman'
                    ]
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'email',
                        'username',
                        'api_token',
                        'id',
                        'profile' => [
                            'first_name',
                            'last_name',
                            'gender',
                            'birthday',
                            'picture_id'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('users', [
            'username' => 'testman',
            'email' => 'test@email.com'
        ]);

        $user_id = $response->decodeResponseJson()['data']['user']['id'];

        $this->assertDatabaseHas('profiles', ['user_id' => $user_id]);
        $this->assertDatabaseHas('user_privacies', ['user_id' => $user_id]);
    }

    public function testLoginFailure(){

        $response = $this->json('POST', '/api/login', ['username' => 'test@email.com', 'password' => 'password']);

        $response->assertJson(['success' => false]);

    }

    public function testLogin(){

        $profile = factory('App\Profile')->create();

        $response = $this->json('POST', '/api/login', [
            'username' => User::find($profile->user_id)->username,
            'password' => 'password'
        ]);

        $response->assertStatus(200);

        $response->assertJson(['success' => true]);

    }

    public function testEmailTaken()
    {
        $user = factory('App\User')->create();
        $response = $this->json('GET', '/api/email_taken/'
        . $user->email, []);

        $response->assertJson([
            'success' => true,
            'data' => [
                'taken' => true
            ]
        ]);

        $response = $this->json('GET', '/api/email_taken/'
            . 'averyrandomemail@email.com', []);
        $response->assertJson([
            'success' => true,
            'data' => [
                'taken' => false
            ]
        ]);
    }

    public function testUsernameTaken()
    {
        $user = factory('App\User')->create();
        $response = $this->json('GET', '/api/username_taken/'
            . $user->username, []);

        $response->assertJson([
            'success' => true,
            'data' => [
                'taken' => true
            ]
        ]);

        $response = $this->json('GET', '/api/email_taken/'
            . 'averyrandomusername', []);
        $response->assertJson([
            'success' => true,
            'data' => [
                'taken' => false
            ]
        ]);
    }

}
