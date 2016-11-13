<?php

namespace App\Transformers;

use App\Post;

/**
 * Transforms a Post into a formatted payload
 */
class PostTransformer implements PayloadTransformer
{

    /**
     * @var Post
     */
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Transform the payload into a formatted array
     *
     * @return Array
     */
    public function transform()
    {
        $post = $this->post; //short alias

        $post->load('author', 'comments.user');

        $comments = $post->comments->map(function($c) {
            return [
                'body' => $c->body,
                'created_by' => $c->user->name,
                'created_at' => $c->created_at->toDateTimeString(),
            ];
        });

        return [
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'created_at' => $post->created_at->toDateTimeString(),
                'author' => $post->author->name,
                'comments' => [
                    'data' => $comments,
                ]
            ]
        ];
    }
}
