<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\User;
use Illuminate\Support\Facades\Hash;


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

        $response = $this->json('POST', '/api/register', ['username' => 'testman', 'gender' => 'gay', 'email' => 'test@email.com', 'password' => 'password', 'password_confirmation' => 'password']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'test@email.com',
                    'username' => 'testman'
                ]
            ]);

    }

    public function testLoginFailure(){

        $response = $this->json('POST', '/api/login', ['username' => 'test@email.com', 'password' => 'password']);

        $response->assertJson(['success' => false]);

    }

    public function testLogin(){

        $user = factory('App\User')->create(['password' => Hash::make('password')]);

        $response = $this->json('POST', '/api/login', ['username' => $user->email, 'password' => 'password']);

        $response->assertStatus(200);

        $response->assertJson(['success' => true]);

    }

}
