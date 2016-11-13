<?php

namespace App\Jobs;

use App\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DetermineAuthorPopularity implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Post
     */
    private $post;

    /**
     * Create a new job instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get number of distinct users who have commented, excluding the author
        $numberOfUsersCommented = $this->post->comments
            ->pluck('user')
            ->unique()
            ->reject($this->post->author)
            ->count();

        // if number of users meets the threshold for user popularity, mark the user as popular
        if ($numberOfUsersCommented >= Post::USER_COMMENTS_TO_SET_AUTHOR_POPULAR && ! $this->post->author->isPopular()) {
            $this->post->author->makePopular();
        }
    }
}
