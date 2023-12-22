<?php

namespace ManeOlawale\Superban\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use ManeOlawale\Superban\Superban;
use ManeOlawale\Superban\SuperbanServiceProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestBench extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    protected function getEnvironmentSetUp($app)
    {
        putenv('CACHE_DRIVER=array');
        putenv('SUPERBAN_DRIVER=array');
        $config = require __DIR__ . '/../config/superban.php';

        $app['config']->set('superban', $config);
        $app['config']->set('cache', [
            'default' => 'array',
            'stores' => [
                'array' => [
                    'driver' => 'array',
                    'serialize' => false,
                ],
                'file' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/storage/',
                ],
            ]
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SuperbanServiceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        /**
         * @var \Illuminate\Routing\Router
         */
        $router = $this->app->make(Router::class);

        $router->get('/index', [TestingController::class, 'index'])->name('app.index');
        $router->get('/superban', [TestingController::class, 'index'])->middleware(['superban'])->name('app.superban');
        $router->get('/superban_route', [TestingController::class, 'index'])->middleware(['superban_route'])->name('app.superban_route');
        $router->get('/superban_custom', [TestingController::class, 'index'])->middleware(['superban:150,3,2880'])->name('app.superban_custom');
        $router->get('/superban_route_custom', [TestingController::class, 'index'])->middleware(['superban_route:150,3,2880'])->name('app.superban_route_custom');
    }

    public function makeRequest(User $user = null, array $server = [], $path = null): Request
    {
        $request = Request::create($path ?? route('app.index'), 'GET', [], [], [], array_merge([
            'REMOTE_ADDR' => '20.10.2020'
        ], $server));

        if ($user) {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        return $request;
    }

    protected function tearDown(): void
    {
        Superban::banResponseUsing();
        Cache::driver('file')->clear();
        Cache::driver('array')->clear();
    }
}
