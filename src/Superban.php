<?php

namespace ManeOlawale\Superban;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Superban
{
    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Banning key
     *
     * @var string
     */
    protected $key;

    /**
     * Banning duration
     *
     * @var string
     */
    protected $duration;

    /**
     * Ban response using callback
     *
     * @var callable
     */
    protected static $banResponseUsing;

    /**
     * Create a superban instance
     *
     * @param string $key
     * @param int $duration
     */
    public function __construct(Request $request, int $duration, string $append = '') {
        $this->request = $request;
        $this->duration = $duration;

        $this->initiateKey($append);
    }

    /**
     * Get superban key
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Perform the banning
     *
     * @return bool
     */
    public function ban(): bool
    {
        return $this->getCache()->put(
            $this->key,
            (string)$this->untill()->getTimestamp(),
            now()->addMinutes($this->duration)
        );
    }

    /**
     * Check if the ban is active
     *
     * @return boolean
     */
    public function banned(): bool
    {
        return $this->getCache()->has($this->key);
    }

    /**
     * Get the time the ban will be lifted
     *
     * @return Carbon
     */
    public function untill(): Carbon
    {
        return $this->banned() ? Carbon::createFromTimestamp($this->getCache()->get($this->key))
                                 : now()->addMinutes($this->duration);
    }

    /**
     * Make the ban response
     *
     * @return Response|string|integer|array
     */
    public function banResponse(): Response|string|int|array
    {
        if ($this->request->expectsJson()) {
            $default = response()->json([
                'message' => sprintf('Sorry, you\'re temporarily banned. Please return after %s.', $this->untill()->format('M d, Y, g:i a')),
                'untill' => (string)$this->untill()
            ], JsonResponse::HTTP_FORBIDDEN);
        } else {
            $default = response(
                sprintf('Sorry, you\'re temporarily banned. Please return after %s.', $this->untill()->format('M d, Y, g:i a')),
                Response::HTTP_FORBIDDEN,
                [
                    'banned-untill' => (string)$this->untill()
                ]
            );
        }

        if (isset(static::$banResponseUsing)) {
            return call_user_func_array(static::$banResponseUsing, [
                $this->request,
                $this->untill(),
                $default
            ]);
        } else {
            return $default;
        }
    }

    /**
     * Fetch the cache driver
     *
     * @return Repository
     */
    protected function getCache(): Repository
    {
        return Cache::driver(config('superban.driver', config('cache.default')));
    }

    /**
     * Initiate the content of the key
     *
     * @param string $append
     * @return void
     */
    protected function initiateKey(string $append)
    {
        $key = 'superban-' . $this->request->ip();

        /**
         * @var \Illuminate\Foundation\Auth\User
         */
        if ($user = $this->request->user()) {
            $key .= '-' . $user->getKey() . '-' . $user->getEmailForVerification();
        }

        $this->key = $key . $append;
    }

    /**
     * Handle ban response
     *
     * @param callable|null $callable
     * @return void
     */
    public static function banResponseUsing(callable $callable = null): void
    {
        static::$banResponseUsing = $callable;
    }
}
