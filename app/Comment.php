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

    protected $fillable = ['body', 'user_id'];
}
