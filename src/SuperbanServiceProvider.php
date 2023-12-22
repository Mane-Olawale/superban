<?php

namespace ManeOlawale\Superban;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;

class SuperbanServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the provider
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            __DIR__ . '/../config/superban.php' => App::configPath('superban.php')

        ], 'superban.config');

        $router->aliasMiddleware('superban', SuperbanMiddleware::class);
        $router->aliasMiddleware('superban_route', SuperbanRouteMiddleware::class);
    }
}
