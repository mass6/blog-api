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
        $comments = factory(Comment::class, 2)->create(['post_id' => $post->id, 'user_id' => $user->id]);

        // When I transform the post
        $payload = (new PostTransformer($post))->transform();

        // Then the post should be transformed into a properly formatted payload
        $this->assertArrayHasKey('data', $payload);
        $data = $payload['data'];

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

        $this->assertArrayHasKey('comments', $data);
        $cData = array_get($data, 'comments.data');
        $this->assertCount(2, $cData);
        $comment = $cData[0];

        $this->assertArrayHasKey('body', $comment);
        $this->assertEquals($comments[0]->body, $comment['body']);

        $this->assertArrayHasKey('created_by', $comment);
        $this->assertEquals($comments[0]->user->name, $comment['created_by']);

        $this->assertArrayHasKey('created_at', $comment);
        $this->assertEquals($comments[0]->created_at, $comment['created_at']);
    }

}
