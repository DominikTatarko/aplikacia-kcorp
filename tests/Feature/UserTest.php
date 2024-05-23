<?php

use App\Http\Controllers\CatchAllController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use function Pest\Laravel\get;
use function Pest\Laravel\mock;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Config::set('proxy.gateway', [
        'url' => 'https://example.com'
    ]);
    Http::fake();
});

test('handles GET request', function () {
    $request = Request::create('/test', 'GET', ['any' => 'test-endpoint'], [], [], [], json_encode(['data' => 'value']));
    $controller = new CatchAllController();

    $response = $controller->handleRequest('gateway', $request);

    Http::assertSent(function ($request) {
        Log::info('Request sent', [
            'url' => $request->url(),
            'method' => $request->method(),
        ]);
        return $request->url() == 'https://example.com/test-endpoint' &&
               $request->method() == 'GET';
    });

    expect($response->status())->toBe(200);
});

test('handles POST request', function () {
    $request = Request::create('/test', 'POST', ['any' => 'test-endpoint']);
    $controller = new CatchAllController();

    $response = $controller->handleRequest('gateway', $request);

    Http::assertSent(function ($request) {
        Log::info('Request sent', [
            'url' => $request->url(),
            'method' => $request->method(),
        ]);
        return $request->url() == 'https://example.com/test-endpoint' &&
               $request->method() == 'POST';
    });

    expect($response->status())->toBe(200);
});

test('removes cookie header', function () {
    $request = Request::create('/test', 'GET', ['any' => 'test-endpoint']);
    $request->headers->set('cookie', 'testcookie');
    $controller = new CatchAllController();

    $response = $controller->handleRequest('gateway', $request);

    Http::assertSent(function ($request) {
        return !isset($request->headers()['cookie']);
    });
});

test('sets host header', function () {
    $request = Request::create('/test', 'GET', ['any' => 'test-endpoint']);
    $request->headers->set('host', 'originalhost.com');
    $controller = new CatchAllController();

    $response = $controller->handleRequest('gateway', $request);

    Http::assertSent(function ($request) {
        return $request->headers()['host'] == ['example.com'];
    });
});

test('returns 404 when gateway is not configured', function () {
    Config::set('proxy.nonexistent', null);
    $request = Request::create('/test', 'GET', ['any' => 'test-endpoint']);
    $controller = new CatchAllController();

    try {
        $response = $controller->handleRequest('nonexistent', $request);
    } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
        $response = response('', 404);
    }

    expect($response->status())->toBe(404);
});
