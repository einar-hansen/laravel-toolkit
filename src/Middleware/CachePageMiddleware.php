<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @link https://andyhinkle.com/blog/using-cloudflare-page-cache-with-laravel
 */
class CachePageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldCacheResponse($request, $response)) {
            $response->headers->add([
                'Cache-Control' => 'max-age=1800, public',
            ]);
        }

        return $response;
    }

    public function shouldCacheResponse(Request $request, Response $response): bool
    {
        if (! app()->isProduction()) {
            return false;
        }

        if (auth()->check()) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        return $response->isSuccessful();
    }
}
