<?php

use App\Comment;
use App\Post;
use App\Transformers\PostTransformer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostTransformerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_transforms_a_post_into_a_formatted_payload()
    {
        $this->withoutNotifications();

        // Given I have a post
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create(['author_id' => $user->id]);

        // When I transform the post
        $data = (new PostTransformer($post))->transform();

        // Then the post should be transformed into a properly formatted payload

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($post->id, $data['id']);

        $this->assertArrayHasKey('title', $data);
        $this->assertEquals($post->title, $data['title']);

        $this->assertArrayHasKey('body', $data);
        $this->assertEquals($post->body, $data['body']);

        $this->assertArrayHasKey('created_at', $data);
        $this->assertEquals($post->created_at, $data['created_at']);

        $this->assertArrayHasKey('author', $data);
        $this->assertEquals($post->author->name, $data['author']);

    }

}
