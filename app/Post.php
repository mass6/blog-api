<?php

namespace App;

use Carbon\Carbon;
use App\Notifications\CommentAdded;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\QuotaExceededException;

class Post extends Model
{
    const USER_COMMENTS_TO_SET_AUTHOR_POPULAR = 6;

    public static $validationRules = [
        'title' => 'required|max:255',
        'body' => 'required',
    ];

    protected $fillable = ['title', 'body'];

    /*
     |--------------------------------------------------------------------------
     | Operations
     |--------------------------------------------------------------------------
     |
     */

    public static function createPost($title, $body, $user)
    {
        $quota = config('api.posts.daily-creation-quota', 5);
        $postsToday = static::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($quota)
            ->get()
            ->filter(function($post){
                return $post->created_at->isToday();
            });

        if($postsToday->count() < $quota) {
            return $user->posts()->create([
                'title' => $title,
                'body' => $body
            ]);
        }

        throw new QuotaExceededException('Daily rate limit exceeded.');
    }



    /**
     * Add a new comment to the post
     *
     * @param $body
     * @param $userId
     *
     * @return Model
     */
    public function addComment($body, $userId)
    {
        $comment = $this->comments()->create([
            'body' => $body,
            'user_id' => $userId
        ]);

        $this->determineAuthorPopularity();

        $this->notifyPostSubscribers($comment);

        return $comment;
    }

    /**
     * A user is considered popular if a post is commented on
     * by the amount of distinct users configured
     * in USER_COMMENTS_TO_SET_AUTHOR_POPULAR
     */
    protected function determineAuthorPopularity()
    {
        // refresh post model
        $post = Post::find($this->id);

        // Get number of distinct users who have commented, excluding the author
        $numberOfUsersCommented = $post->comments
            ->pluck('user')
            ->unique()
            ->reject($post->author)
            ->count();

        // if number of users meets the threshold for user popularity, mark the user as popular
        if ($numberOfUsersCommented >= Post::USER_COMMENTS_TO_SET_AUTHOR_POPULAR && ! $post->author->isPopular()) {
            $post->author->makePopular();
        }
    }

    /**
     * Send notifications of new comment to users subscribed to the post
     *
     * @param Comment $comment
     */
    protected function notifyPostSubscribers(Comment $comment)
    {
        $delay = Carbon::now()->addMinutes(1);

        // Notify Users
        $this->comments
            ->pluck('user')
            ->merge([$this->author])
            ->unique()
            ->reject($comment->user)
            ->each(function($user) use ($comment, $delay) {
                $user->notify((new CommentAdded($comment))->delay($delay));
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     |
     */

    /**
     * Relation: post is authored by a single user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relation: A post has many comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
