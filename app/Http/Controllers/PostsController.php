<?php

namespace App\Http\Controllers;

use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use App\Transformers\PostTransformer;

/**
 * Class PostsController
 * @package App\Http\Controllers
 */
class PostsController extends Controller
{

    /**
     * @var Post
     */
    private $post;

    /**
     * PostsController constructor.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Store a new post.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, Post::$validationRules);

        $post = $request->user()->createPost($request->get('title'), $request->get('body'));

        return response()->json(['id' => $post->id], 201);
    }

    /**
     * Retrieve a  post.
     *
     * @param Post $post
     *
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Post $post)
    {
        return response()->json((new PostTransformer($post))->transform(), 200);
    }

    /**
     * Update a post. Only allow if user is the post author.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Post                      $post
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        if ($post->author->id !== $request->user()->id) {
            return response('Unauthorized', 401);
        }
        $this->validate($request, Post::$validationRules);

        $post->update([
            'title' => $request->get('title'),
            'body' => $request->get('body')
        ]);
    }

    /**
     * Comment on a post.
     *
     * @param Post                      $post
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function comment(Post $post, Request $request)
    {
        $this->validate($request, Comment::$validationRules);

        $comment = $post->addComment($request->get('body'), $request->user()->id);

        return response()->json(['id' => $comment->id], 201);
    }

    /**
     * Delete a post. Only allow if user is the post author.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Post                      $post
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Post $post)
    {
        if ($post->author->id !== $request->user()->id) {
            return response('Unauthorized', 401);
        }

        $post->delete();
    }
}
