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
            $data = (new PostTransformer($post))->transform();

            return $this->appendCommentsCount($data, $post);

        })->toArray();
    }

    protected function appendCommentsCount($data, $post)
    {
        return array_merge($data, ['comments_count' => $post->comments()->count()]);
    }

}
