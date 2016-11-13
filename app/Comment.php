<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * A Comment represents a textual comment that a user can add to a Post
 *
 * @package App
 */
class Comment extends Model
{
    public static $validationRules = [
        'body' => 'required',
    ];

    protected $fillable = ['body', 'post_id', 'user_id'];

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     |
     */

    /**
     * Relation: A comment belongs to a post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Relation: A comment belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
