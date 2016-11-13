<?php

namespace App;

use App\Notifications\CommentAdded;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
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

    public function addComment($data)
    {
        $comment = $this->comments()->create($data);

        $this->notifyPostSubscribers($comment);
    }

    /**
     * Send notifications of new comment to users subscribed to the post
     *
     * @param Comment $comment
     */
    protected function notifyPostSubscribers(Comment $comment)
    {
        // Notify Users
        $this->comments
            ->pluck('user')
            ->merge([$this->author])
            ->unique()
            ->reject($comment->user)
            ->each(function($user) use ($comment) {
                $user->notify(new CommentAdded($comment));
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
