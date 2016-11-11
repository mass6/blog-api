<?php

use App\Post;
use App\User;

class PostTest extends PassportTestCase
{

    /** @test */
    public function a_visitor_can_read_a_post()
    {
        // Given there is a post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When a visitor tries to access the post resource
        $this->get('/api/posts/' . $post->id);
        //$response = $this->call('GET', '/api/posts/' . $post->id);

        // Then he should she the post details
        $this->assertResponseOk();
        $read = json_decode($this->response->getContent());
        $this->assertEquals($post->id, $read->id);
        $this->assertEquals($post->body, $read->body);
    }

    /** @test */
    public function a_user_can_create_a_blog_post()
    {
        // When I post a new blog post to the api
        $post = factory(Post::class)->make();
        $this->postJson('/api/posts', $post->toArray(), $this->headers);

        // Then a new blog post should be created and stored in the database
        $this->assertResponseOk();
        $saved = Post::first();
        $this->assertEquals($post->title, $saved->title);
        $this->assertEquals($post->body, $saved->body);
    }

    /** @test */
    public function a_blog_post_must_pass_validation()
    {
        // When I attempt to create new posts with invalid data...

        // Title missing
        $this->postJson('/api/posts', factory(Post::class)->make(['title' => ''])->toArray(), $this->headers);
        $this->assertEquals(422, $this->response->getStatusCode());

        // Body missing
        $this->postJson('/api/posts', factory(Post::class)->make(['body' => ''])->toArray(), $this->headers);
        $this->assertEquals(422, $this->response->getStatusCode());

        // Title to long
        $stringOver255Chars = 'mnJhb9Hwz6wGY3NX6vOyJv2GbVtYmh5Wh1L1nWlkJAnl2DUcg1vwFqCoWhoJPn46Km6CgmHUVw9RwtHLQYwSWeGgBqDDRE8Xo36SGEYQZIfapE14BIfReRL7rFz9FPzx5gZzMHNEZBmmzzjtWFhVJSGoYspSKWkGMxLNt3wqH4cslOkHOu6f2oLYnroq9Hc97PL4SBwrRNyVRs2vzZycvbu000r2s0RcFXlaOpcTANYBH3LS94UtJ0PlSIqnwnew';
        $this->postJson('/api/posts', factory(Post::class)->make(['title' => $stringOver255Chars])->toArray(), $this->headers);
        $this->assertEquals(422, $this->response->getStatusCode());
    }

    /** @test */
    public function a_user_can_update_a_post()
    {
        // Given there is an existing post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When I update the post's title and body
        $updates = ['title' => 'foo', 'body' => 'bar'];
        $this->patchJson('/api/posts/' . $post->id, $updates, $this->headers);

        // Then the changes should reflect in the database
        $this->assertResponseOk();
        $retrieved = Post::first();
        $this->assertEquals($updates['title'], $retrieved->title);
        $this->assertEquals($updates['body'], $retrieved->body);
    }

    /** @test */
    public function a_user_cannot_update_another_users_post()
    {
        // Given there is an existing post from a another user
        $post = factory(User::class)->create()->posts()->create(factory(Post::class)->make()->toArray());

        // When I try to update the post that was created by another user
        $updates = ['title' => 'foo', 'body' => 'bar'];
        $this->patchJson('/api/posts/' . $post->id, $updates, $this->headers);

        // Then I should receive an "Unauthorized" response, and the changes should not be saved
        $this->assertEquals(401, $this->response->getStatusCode());
        $retrieved = Post::first();
        $this->assertEquals($post->title, $retrieved->title);
        $this->assertEquals($post->body, $retrieved->body);
    }

    /** @test */
    public function a_user_can_delete_his_own_post()
    {
        // Given there is an existing post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When I set a delete request
        $this->deleteJson('/api/posts/' . $post->id, [], $this->headers);

        // Then the post should be deleted
        $this->assertResponseOk();
        $this->assertEmpty(Post::all(), 'The post should be deleted.');
    }

    /** @test */
    public function a_user_cannot_delete_another_users_post()
    {
        // Given there is an existing post from a another user
        $post = factory(User::class)->create()->posts()->create(factory(Post::class)->make()->toArray());

        // When I try to delete the post that was created by another user
        $this->deleteJson('/api/posts/' . $post->id, [], $this->headers);

        // Then I should receive an "Unauthorized" response, and the post should not be deleted
        $this->assertEquals(401, $this->response->getStatusCode());
        $posts = Post::first();
        $this->assertEquals(1, $posts->count());
    }

    /** @test */
    public function a_user_is_not_allowed_to_exceed_his_daily_post_quota()
    {
        // Given a user who has reached his daily post quota
        $quota = config('api.rate-limit.posts', 5);
        factory(Post::class, $quota)->create(['author_id' => $this->user->id]);
        // When the user attempts to create an additional post
        $this->postJson('/api/posts', ['title' => 'foo', 'body' => 'bar'], $this->headers);

        // Then the user's request should be rejected,
        // and should return a 403 "QuotaExceededException" response
        $this->assertEquals(403, $this->response->getStatusCode());
        $this->assertCount($quota, Post::all());
    }
}
