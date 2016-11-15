<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $data = [
            'user' => $request->user() ? $request->user()->id : 'visitor',
            'path' => $request->path(),
            'parameters' => $request->getContent(),
        ];

        Log::info($data);

        return $response;
    }
}
