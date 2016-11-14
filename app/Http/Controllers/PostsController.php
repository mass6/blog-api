<?php

namespace App\Http\Controllers;

use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use App\Transformers\PostTransformer;
use App\Transformers\PaginatedPostsTransformer;

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
     * Return a paginated list of posts
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $posts = $this->getPaginatedPosts($request);

        return response()->json((new PaginatedPostsTransformer($posts))->transform(), 200);
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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->validate($request, [
            'title' => 'min:1|max:255',
            'body' => 'min:1',
        ]);

        $data = collect($request->only('title', 'body'))
            ->reject(function($key) {
                return $key == null;
            });

        $post->update($data->toArray());
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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $post->delete();
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getPaginatedPosts(Request $request)
    {
        $maxAllowed = config('api.posts.pagination.perPageMax', 50);
        $perPage    = $request->get('perPage', config('api.posts.pagination.perPageDefault', 20));
        $perPage    = $perPage <= $maxAllowed ? $perPage : $maxAllowed;

        $posts = Post::orderBy('created_at', 'desc')->paginate($perPage);


        return $posts;
    }
}
