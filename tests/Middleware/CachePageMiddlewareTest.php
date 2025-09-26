<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Middleware;

use EinarHansen\Toolkit\Middleware\CachePageMiddleware;
use EinarHansen\Toolkit\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Override;
use PHPUnit\Framework\Attributes\Test;

class CachePageMiddlewareTest extends TestCase
{
    private CachePageMiddleware $middleware;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CachePageMiddleware;
    }

    #[Test]
    public function it_adds_cache_headers_for_valid_requests_in_production(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_does_not_cache_when_not_in_production(): void
    {
        $this->app['env'] = 'local';

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertNotEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_does_not_cache_when_user_is_authenticated(): void
    {
        $this->app['env'] = 'production';

        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertNotEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_does_not_cache_non_get_requests(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'POST');
        $response = new Response('Test content', 200);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertNotEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_does_not_cache_unsuccessful_responses(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'GET');
        $response = new Response('Not found', 404);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertNotEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_does_not_cache_server_error_responses(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'GET');
        $response = new Response('Server error', 500);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertNotEquals('max-age=1800, public', $result->headers->get('Cache-Control'));
    }

    #[Test]
    public function should_cache_response_returns_false_for_non_production(): void
    {
        $this->app['env'] = 'local';

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $this->assertFalse($this->middleware->shouldCacheResponse($request, $response));
    }

    #[Test]
    public function should_cache_response_returns_false_for_authenticated_users(): void
    {
        $this->app['env'] = 'production';

        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $this->assertFalse($this->middleware->shouldCacheResponse($request, $response));
    }

    #[Test]
    public function should_cache_response_returns_false_for_non_get_requests(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'POST');
        $response = new Response('Test content', 200);

        $this->assertFalse($this->middleware->shouldCacheResponse($request, $response));
    }

    #[Test]
    public function should_cache_response_returns_false_for_unsuccessful_responses(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/test', 'GET');
        $response = new Response('Not found', 404);

        $this->assertFalse($this->middleware->shouldCacheResponse($request, $response));
    }

    #[Test]
    public function should_cache_response_returns_true_for_valid_conditions(): void
    {
        $this->app['env'] = 'production';

        Auth::shouldReceive('check')->once()->andReturn(false);

        $request = Request::create('/test', 'GET');
        $response = new Response('Test content', 200);

        $this->assertTrue($this->middleware->shouldCacheResponse($request, $response));
    }
}
