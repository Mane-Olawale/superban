<?php

namespace ManeOlawale\Superban;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SuperbanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $limit = 200, $range = 2, $duration = 1440): Response
    {
        $superban = $this->makeSuperban($request, (int)$duration);

        if ($superban->banned()) {
            return $superban->banResponse();
        }

        $response = RateLimiter::attempt(
            'ratelimiter-' . $superban->key(),
            (int)$limit,
            function () use ($next, $request) {
                return $next($request);
            },
            (int)$range * 60
        );

        if (!$response) {
            $superban->ban();
            return $superban->banResponse();
        }

        return $response;
    }
    
    /**
     * Create a superban instance
     *
     * @param Request $request
     * @param integer $duration
     * @return Superban
     */
    protected function makeSuperban(Request $request, int $duration): Superban
    {
        return new Superban($request, $duration);
    }
}
