<?php

namespace App\Transformers;

use Illuminate\Support\Collection;

class CommentCollectionTransformer implements PayloadTransformer
{

    /**
     * @var Collection
     */
    private $comments;

    public function __construct(Collection $comments)
    {
        $this->comments = $comments;
    }
    /**
     * Transform the payload into a formatted array
     *
     * @return Array
     */
    public function transform()
    {
        return $this->comments->map(function($c) {
            return [
                'body' => $c->body,
                'created_by' => $c->user->name,
                'created_at' => $c->created_at->toDateTimeString(),
            ];
        });
    }
}
