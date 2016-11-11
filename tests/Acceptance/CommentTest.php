<?php

use App\Post;
use App\User;

class CommentTest extends PassportTestCase
{

    /** @test */
    public function a_user_can_comment_on_a_post()
    {
        // Given there is a post
        $post = factory(User::class)->create()->posts()->create(factory(Post::class)->make()->toArray());

        // When I submit a post comment
        $this->postJson('/api/posts/'.$post->id.'/comments', ['body' => 'foobarbaz'], $this->headers);

        // Then the comment should be saved
        $this->assertResponseOk();
        $this->assertCount(1, $post->comments);
        $this->assertEquals('foobarbaz', $post->comments->first()->body);
    }

    /** @test */
    public function a_comment_must_pass_validation()
    {
        // Given there is a post
        $post = factory(User::class)->create()->posts()->create(factory(Post::class)->make()->toArray());

        // When I attempt to create a comment with an empty text body
        $this->postJson('/api/posts/'.$post->id.'/comments', ['body' => ''], $this->headers);

        // Then the I should receive a 422 error response
        $this->assertEquals(422, $this->response->getStatusCode());

        // And the comment should not be saved
        $this->assertCount(0, $post->comments);
    }
}
