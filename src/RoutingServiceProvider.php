<?php

namespace MorningMedley\Route;

class RoutingServiceProvider extends \Illuminate\Routing\RoutingServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/config.php", 'route');

        parent::register();
    }

    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
    }

    public function boot()
    {
        $this->app->make('router')->boot();
    }
}
