<?php

use App\Post;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_visitor_can_read_a_post()
    {
        // Given there is a post
        $post = factory(Post::class)->create([]);

        // When a visitor tries to access the post resource
        $response = $this->call('GET', '/api/posts/' . $post->id);

        // Then he should she the post details
        $this->assertResponseOk();
        $read = json_decode($response->getContent());
        $this->assertEquals($post->id, $read->id);
        $this->assertEquals($post->body, $read->body);
    }
}
