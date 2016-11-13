<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the user's popularity
     *
     * @return bool
     */
    public function isPopular()
    {
        return (bool) $this->popular;
    }

    /**
     * Set the user to popular
     */
    public function makePopular()
    {
        $this->popular = true;
        $this->save();
    }


    /*
     |--------------------------------------------------------------------------
     | Operations
     |--------------------------------------------------------------------------
     |
     */

    /**
     * Create a user post
     *
     * @param $title
     * @param $body
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createPost($title, $body)
    {
        return $this->posts()->create([
            'title' => $title,
            'body' => $body
        ]);
    }

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     |
     */

    /**
     * Relation: A user has many posts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
