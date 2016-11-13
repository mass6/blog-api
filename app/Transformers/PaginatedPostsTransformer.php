<?php

namespace App\Transformers;

use Illuminate\Contracts\Pagination\Paginator;

/**
 * Receives a paginated collection of posts and returns a formatted payload
 */
class PaginatedPostsTransformer implements PayloadTransformer
{

    /** @var Paginator  */
    private $posts;

    public function __construct(Paginator $posts)
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
        $posts = (new PostCollectionTransformer($this->posts->getCollection()))->transform();

        return [
            'data' => $posts,
            'total' => $this->posts->total(),
            'per_page' => $this->posts->perpage(),
            'current_page' => $this->posts->currentPage(),
            'last_page' => $this->posts->lastpage(),
            'next_page_url' => $this->posts->nextPageUrl(),
            'prev_page_url' => $this->posts->previousPageUrl(),
            'from' => $this->posts->firstItem(),
            'to' => $this->posts->lastItem(),
        ];
    }
}
