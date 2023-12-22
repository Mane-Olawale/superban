<?php

namespace ManeOlawale\Superban\Tests;

use Illuminate\Http\Response;
use ManeOlawale\Superban\SuperbanMiddleware;
use ManeOlawale\Superban\SuperbanRouteMiddleware;
use Spatie\TestTime\TestTime;

class MiddlewareTest extends TestBench
{
    public function testMiddlewareCanPassThrough(): void
    {
        $next = function () {
            return response('The light at the end of the tunnel.');
        };

        // For Global
        $global = new SuperbanMiddleware();
        $responseOne = $global->handle($this->makeRequest(), $next);

        // For route
        $route = new SuperbanRouteMiddleware();
        $responseTwo = $route->handle($this->makeRequest(), $next);

        // Then
        $this->assertEquals(Response::HTTP_OK, $responseOne->getStatusCode());
        $this->assertEquals('The light at the end of the tunnel.', $responseOne->getContent());
        $this->assertEquals(Response::HTTP_OK, $responseTwo->getStatusCode());
        $this->assertEquals('The light at the end of the tunnel.', $responseTwo->getContent());
    }

    public function testMiddlewareWithTwoHundredRequest(): void
    {
        $next = function () {
            return response('The light at the end of the tunnel.');
        };
        
        $now = TestTime::freeze();
        // For Global
        $global = new SuperbanMiddleware();
        for ($i=0; $i < 200; $i++) {
            $response = $global->handle($this->makeRequest(), $next);
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals('The light at the end of the tunnel.', $response->getContent());
        }

        $response = $global->handle($this->makeRequest(), $next);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(
            'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.',
            $response->getContent()
        );

        TestTime::addDays(2);
        $response = $global->handle($this->makeRequest(), $next);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('The light at the end of the tunnel.', $response->getContent());
    }

    public function testMiddlewareWithTwoHundredRequestOnSuperbanRoute(): void
    {
        $next = function () {
            return response('The light at the end of the tunnel.');
        };
        $request = $this->makeRequest(null, [], 'custom_path');
        
        $now = TestTime::freeze();
        // For Global
        $routeMiddleware = new SuperbanRouteMiddleware();
        $this->assertSame('superban-20.10.2020:custom_path', invade($routeMiddleware)->makeSuperban($request, 1440)->key());

        for ($i=0; $i < 200; $i++) {
            $response = $routeMiddleware->handle($request, $next);
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals('The light at the end of the tunnel.', $response->getContent());
        }

        $response = $routeMiddleware->handle($request, $next);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(
            'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.',
            $response->getContent()
        );

        TestTime::addDays(2);
        $response = $routeMiddleware->handle($this->makeRequest(), $next);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('The light at the end of the tunnel.', $response->getContent());
    }
}
