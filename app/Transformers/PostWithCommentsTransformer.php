<?php

namespace App\Transformers;

use App\Post;

/**
 * Transforms a Post into a formatted payload
 */
class PostWithCommentsTransformer implements PayloadTransformer
{

    const COMMENT_LIMIT = 3;

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

        $comments = (new CommentCollectionTransformer($post->comments()->limit(self::COMMENT_LIMIT)->get()))->transform();

        $post = (new PostTransformer($post))->transform();

        return [
            'data' => array_merge($post, ['comments' => $comments]),
        ];
    }
}
