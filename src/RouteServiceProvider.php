<?php

namespace MorningMedley\Route;

use Illuminate\Foundation\Console\RouteCacheCommand;
use Illuminate\Foundation\Console\RouteClearCommand;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;

class RouteServiceProvider extends \Illuminate\Foundation\Support\Providers\RouteServiceProvider
{
    public function boot()
    {
        $this->namespace = $this->app->make('config')->get('route.controller_namespace');
        $this->commands([
            RouteCacheCommand::class,
            RouteClearCommand::class,
            RouteListCommand::class,
        ]);
    }

    public function map(Finder $finder)
    {
        // TODO: Look into middleware groups
        // Load route files
        $routesDir = $this->app->basePath('routes');
        $finder->in($routesDir)->name('*.php')->notName('index.php')->files();
        foreach ($finder as $file) {
            Route::namespace($this->namespace)
                ->group($file->getRealPath());
        }
    }
}
