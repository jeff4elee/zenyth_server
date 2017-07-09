<?php

namespace Tests\Feature;

use App\Entity;
use App\User;
use App\Like;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LikesAndCommentsTest extends TestCase
{

    public function setUp()
    {
        
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->createApplication();

        Artisan::call('migrate:refresh');

        Artisan::call('db:seed', ['--class' => 'LikesCommentsTableSeeder']);
        
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLikeCreation()
    {

        $user = User::first();
        $entity = factory('App\Entity')->create();

        $this->assertEquals(0, $entity->likesCount());

        $this->json('POST', '/api/like', ['entity_id' => $entity->id], ['Authorization' => 'bearer ' .$user->api_token]);

        $this->assertEquals(1, $entity->likesCount());

    }

    public function testLikeDeletion()
    {

        $like = Like::first();

        $entity = Entity::find($like->entity_id);
        $numLikes = $entity->likesCount();

        $this->json('DELETE', '/api/like/' . $entity->id, [], ['Authorization' => 'bearer ' .$like->user->api_token]);

        $this->assertEquals($numLikes-1, $entity->likesCount());

    }

    public function testCommentCreation()
    {

        $user = factory('App\User')->create();

        $entity = factory('App\Entity')->create();

        $this->assertEquals(0, $entity->commentsCount());

        $response = $this->json('POST', '/api/comment', [
            'on_entity_id' => $entity->id,
            'comment' => 'test comment'
        ], ['Authorization' => 'bearer ' . $user->api_token]);

        $response->assertStatus(200);
        $this->assertEquals(1, $entity->commentsCount());

    }

    public function testCommentRead()
    {

        do {
            $entity = Entity::inRandomOrder()->first();
        } while ($entity->commentsCount() == 0);

        $comment = $entity->comments->first();

        $this->json('GET', '/api/comment/' . $comment->id);

        $this->assertEquals(1, $entity->commentsCount());

    }

    public function testCommentUpdate()
    {

        $comment = factory('App\Comment')->create();

        $text = $comment->comment;

        $response = $this->json('POST', '/api/comment/' . $comment->id, ['comment' => 'NewText!!'],
            ['Authorization' => 'bearer ' . User::find($comment->user_id)->api_token]);

        $response->assertStatus(200);

        $comment_array = $response->json();
        $this->assertNotEquals($text, $comment_array['data']['comment']);
        $this->assertEquals('NewText!!', $comment_array['data']['comment']);
        $this->assertDatabaseHas('comments', ['comment' => 'NewText!!']);

    }

    public function testCommentDelete()
    {

        $comment = factory('App\Comment')->create();

        $this->json('DELETE', '/api/comment/' . $comment->id, [],
            ['Authorization' => 'bearer ' .$comment->user->api_token]);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        Artisan::call('migrate:refresh');
    }

}
