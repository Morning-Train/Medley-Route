<?php

namespace MorningMedley\Route;

use Symfony\Component\Finder\Finder;

class RouteServiceProvider extends \Illuminate\Foundation\Support\Providers\RouteServiceProvider
{
    public function boot()
    {
        $this->namespace = $this->app->make('config')->get('route.controller_namespace');
    }

    public function map(Finder $finder)
    {
        // TODO: Look into middleware groups
        // Load route files
        $routesDir = $this->app->basePath('routes');
        $finder->in($routesDir)->name('*.php')->notName('index.php')->files();
        foreach ($finder as $file) {
            require $file->getRealPath();
        }
    }

    protected function routesAreCached()
    {
        return false; // TODO:
    }

    protected function loadCachedRoutes()
    {
        // TODO:
    }
}
