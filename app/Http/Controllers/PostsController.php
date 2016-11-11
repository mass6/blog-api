<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

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

        $request->user()->createPost([
            'title' => $request->get('title'),
            'body' => $request->get('body')
        ]);
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
        //return $post->author;
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'created_at' => $post->created_at->toDateTimeString(),
            'author' => $post->author->name
        ];

        return response()->json($data, 200);
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
