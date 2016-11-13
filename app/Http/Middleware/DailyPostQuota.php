<?php

namespace App\Http\Middleware;

use App\Exceptions\QuotaExceededException;
use App\Post;
use Closure;

class DailyPostQuota
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws QuotaExceededException
     */
    public function handle($request, Closure $next)
    {
        // Determine if daily quota has been reached
        $quota = config('api.posts.daily-creation-quota', 5);
        $postsToday = Post::where('author_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit($quota)
            ->get()
            ->filter(function($post){
                return $post->created_at->isToday();
            });

        if($postsToday->count() < $quota) {
            return $next($request);
        }

        throw new QuotaExceededException('Daily rate limit exceeded.');
    }
}
