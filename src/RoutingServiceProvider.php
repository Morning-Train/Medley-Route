<?php

namespace MorningMedley\Route;

use Illuminate\Support\Facades\Route;

class RoutingServiceProvider extends \Illuminate\Routing\RoutingServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/config.php", 'route');

        parent::register();

        Route::macro('routesAreCached', function () {
            return $this->routesAreCached();
        });
    }

    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
        // Make sure this router is used
        $this->app->alias('router', \Illuminate\Routing\Router::class);
    }

    public function boot()
    {
        $this->app->make('router')->boot();
    }
}
