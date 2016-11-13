<?php

namespace App\Transformers;

use Illuminate\Support\Collection;

class PostCollectionTransformer implements PayloadTransformer
{

    /**
     * @var Collection
     */
    private $posts;

    public function __construct(Collection $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Transform the payload into a formatted array
     *
     * @return Array
     */
    public function transform()
    {
        return $this->posts->map(function($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'created_at' => $post->created_at->toDateTimeString(),
                'author' => $post->author->name,
                'comments_count' => $post->comments()->count(),
            ];
        })->toArray();
    }
}
