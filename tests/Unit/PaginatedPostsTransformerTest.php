<?php

use App\User;
use App\Post;
use App\Comment;
use App\Transformers\PaginatedPostsTransformer;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PaginatedPostsTransformerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_transforms_a_paginated_list_of_posts_into_a_formatted_payload()
    {
        $this->withoutNotifications();

        // Given I have paginated posts
        $user = factory(User::class)->create();
        $newPosts = factory(Post::class, 20)->create(['author_id' => $user->id]);
        $newPosts->each(function($post) use ($user) {
            factory(Comment::class, 2)->create(['post_id' => $post->id, 'user_id' => $user->id]);
        });
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);

        // When I transform the post collection
        $payload = (new PaginatedPostsTransformer($posts))->transform();

        // Then the post should be transformed into a properly formatted payload
        $this->assertArrayHasKey('data', $payload);
        $data = $payload['data'];
        $this->assertCount(10, $data);

        $post = Post::first();

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertEquals($post->id, $data[0]['id']);

        $this->assertArrayHasKey('title', $data[0]);
        $this->assertEquals($post->title, $data[0]['title']);

        $this->assertArrayHasKey('body', $data[0]);
        $this->assertEquals($post->body, $data[0]['body']);

        $this->assertArrayHasKey('created_at', $data[0]);
        $this->assertEquals($post->created_at->toDateTimeString(), $data[0]['created_at']);

        $this->assertArrayHasKey('author', $data[0]);
        $this->assertEquals($post->author->name, $data[0]['author']);

        $this->assertArrayHasKey('comments_count', $data[0]);
        $this->assertEquals($post->comments()->count(), $data[0]['comments_count']);

        $this->assertArrayHasKey('total', $payload);
        $this->assertEquals(20, $payload['total']);

        $this->assertArrayHasKey('per_page', $payload);
        $this->assertEquals(10, $payload['per_page']);

        $this->assertArrayHasKey('current_page', $payload);
        $this->assertEquals(1, $payload['current_page']);

        $this->assertArrayHasKey('last_page', $payload);
        $this->assertEquals(20, $payload['total']);

        $this->assertArrayHasKey('next_page_url', $payload);
        $this->assertEquals('http://blog-api.app?page=2', $payload['next_page_url']);

        $this->assertArrayHasKey('prev_page_url', $payload);
        $this->assertEquals(null, $payload['prev_page_url']);

        $this->assertArrayHasKey('from', $payload);
        $this->assertEquals(1, $payload['from']);

        $this->assertArrayHasKey('to', $payload);
        $this->assertEquals(10, $payload['to']);
    }

}
