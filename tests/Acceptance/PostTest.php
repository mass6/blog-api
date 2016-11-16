<?php

use App\Post;
use App\User;

class PostTest extends TestCase
{

    /** @test */
    public function a_visitor_can_read_a_post()
    {
        // Given there is a post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When a visitor tries to access the post resource
        $this->getJson('/api/posts/' . $post->id, $this->getHeaders());

        // Then he should see the post details
        $this->seeStatusCode(200);

        $this->seeJson([
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'created_at' => $post->created_at->toDateTimeString(),
                'author' => $post->author->name,
                'comments' => [],
            ]
        ]);
    }

    /** @test */
    public function a_visitor_can_read_all_posts()
    {
        // Given there are posts
        $posts = $this->user->posts()->createMany(factory(Post::class, 2)->make()->toArray());

        // When a visitor tries to retrieve all recent posts
        $this->getJson('/api/posts', $this->getHeaders());

        // Then he should see a paginated list of posts
        $this->seeStatusCode(200);
        $this->assertCount(2, json_decode($this->response->getContent())->data);
        $this->seeJson([
            'total' => 2
        ]);
    }

    /** @test */
    public function test_it_requires_an_api_key_to_create_a_blog_post()
    {
        $this->postJson('/api/posts', [], $this->getHeaders());
        $this->seeStatusCode(401);
    }

    /** @test */
    public function a_user_can_create_a_blog_post()
    {
        // When I post a new blog post to the api
        $post = factory(Post::class)->make();
        $this->postJson('/api/posts', $post->toArray(), $this->getHeadersWithToken());

        // Then a new blog post should be created and stored in the database
        $this->seeStatusCode(201);
        $saved = $this->user->posts()->first();
        $this->assertEquals($post->title, $saved->title);
        $this->assertEquals($post->body, $saved->body);
    }

    /** @test */
    public function a_blog_post_must_pass_validation()
    {
        // When I attempt to create new posts with invalid data...

        // Title missing
        $this->postJson('/api/posts', factory(Post::class)->make(['title' => ''])->toArray(), $this->getHeadersWithToken());
        $this->seeStatusCode(422);

        // Body missing
        $this->postJson('/api/posts', factory(Post::class)->make(['body' => ''])->toArray(), $this->getHeadersWithToken());
        $this->seeStatusCode(422);

        // Title to long
        $stringOver255Chars = 'mnJhb9Hwz6wGY3NX6vOyJv2GbVtYmh5Wh1L1nWlkJAnl2DUcg1vwFqCoWhoJPn46Km6CgmHUVw9RwtHLQYwSWeGgBqDDRE8Xo36SGEYQZIfapE14BIfReRL7rFz9FPzx5gZzMHNEZBmmzzjtWFhVJSGoYspSKWkGMxLNt3wqH4cslOkHOu6f2oLYnroq9Hc97PL4SBwrRNyVRs2vzZycvbu000r2s0RcFXlaOpcTANYBH3LS94UtJ0PlSIqnwnew';
        $this->postJson('/api/posts', factory(Post::class)->make(['title' => $stringOver255Chars])->toArray(), $this->getHeadersWithToken());
        $this->seeStatusCode(422);
    }

    /** @test */
    public function test_it_requires_an_api_key_to_update_a_blog_post()
    {
        // Given there is an existing post
        $this->user->posts()->create(factory(Post::class)->make()->toArray());

        $this->patchJson('/api/posts/1', $this->getHeaders());
        $this->seeStatusCode(401);
    }

    /** @test */
    public function a_user_can_update_a_post()
    {
        // Given there is an existing post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When I update the post's title and body
        $updates = ['title' => 'foo', 'body' => 'bar'];
        $this->patchJson('/api/posts/' . $post->id, $updates, $this->getHeadersWithToken());

        // Then the changes should reflect in the database
        $this->seeStatusCode(200);
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
        $this->patchJson('/api/posts/' . $post->id, $updates, $this->getHeadersWithToken());

        // Then I should receive an "Unauthorized" response, and the changes should not be saved
        $this->seeStatusCode(401);
        $retrieved = Post::first();
        $this->assertEquals($post->title, $retrieved->title);
        $this->assertEquals($post->body, $retrieved->body);
    }

    /** @test */
    public function test_it_requires_an_api_key_to_delete_a_blog_post()
    {
        // Given there is an existing post
        $this->user->posts()->create(factory(Post::class)->make()->toArray());

        $this->deleteJson('/api/posts/1', $this->getHeaders());
        $this->seeStatusCode(401);
    }

    /** @test */
    public function a_user_can_delete_his_own_post()
    {
        // Given there is an existing post
        $post = $this->user->posts()->create(factory(Post::class)->make()->toArray());

        // When I set a delete request
        $this->deleteJson('/api/posts/' . $post->id, [], $this->getHeadersWithToken());

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
        $this->deleteJson('/api/posts/' . $post->id, [], $this->getHeadersWithToken());

        // Then I should receive an "Unauthorized" response, and the post should not be deleted
        $this->seeStatusCode(401);
        $posts = Post::first();
        $this->assertEquals(1, $posts->count());
    }

    /** @test */
    public function a_user_is_not_allowed_to_exceed_his_daily_post_quota()
    {
        // Given a user who has reached his daily post quota
        $quota = config('api.posts.daily-creation-quota', 5);
        factory(Post::class, $quota)->create(['author_id' => $this->user->id]);
        // When the user attempts to create an additional post
        $this->postJson('/api/posts', ['title' => 'foo', 'body' => 'bar'], $this->getHeadersWithToken());

        // Then the user's request should be rejected,
        // and should return a 403 "QuotaExceededException" response
        $this->seeStatusCode(403);
        $this->assertCount($quota, Post::all());
    }

}
