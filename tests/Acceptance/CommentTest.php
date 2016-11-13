<?php

use App\Post;
use App\User;
use App\Comment;
use App\Notifications\CommentAdded;
use Illuminate\Support\Facades\Notification;

class CommentTest extends PassportTestCase
{

    /** @test */
    public function a_user_can_comment_on_a_post()
    {
        $this->withoutNotifications();

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

    /** @test */
    public function it_notifies_all_post_subscribers_when_a_new_comment_is_made()
    {
        Notification::fake();

        // Given there is a post
        $author = factory(User::class)->create();
        $post = $author->posts()->create(factory(Post::class)->make()->toArray());

        // And two users have add comments to the post
        $commentor1 = factory(User::class)->create();
        $commentor2 = factory(User::class)->create();
        Comment::create(['post_id' => $post->id, 'user_id' => $commentor1->id, 'body' => 'comment 1']);
        Comment::create(['post_id' => $post->id, 'user_id' => $commentor2->id, 'body' => 'comment 2']);

        // When I add a new comment on the post
        $this->postJson('/api/posts/'.$post->id.'/comments', ['body' => 'foobar comment'], $this->headers);

        // Then the post author and the two commentors should receive notifications
        Notification::assertSentTo(
            [$author, $commentor1, $commentor2], CommentAdded::class
        );
    }
}
