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

        // Add macro that gives access to information that WordPress needs when adding rewrite rule and tags
        \Illuminate\Routing\Route::macro('rewriteParameters', function () {
            $parameterNames = $this->parameterNames();
            $rewriteParameters = [];

            if (empty($parameterNames)) {
                return $rewriteParameters;
            }

            $optionalParameters = array_keys($this->getOptionalParameterNames());

            foreach ($parameterNames as $parameterName) {
                $regex = key_exists($parameterName, $this->wheres)
                    ? '(' . $this->wheres[$parameterName] . ')'
                    : '([^/]+)';
                $rewriteParameters[] = [
                    'name' => $parameterName,
                    'regex' => $regex,
                    'optional' => in_array($parameterName, $optionalParameters),
                ];
            }

            return $rewriteParameters;
        });
    }

    public function map(Finder $finder)
    {
        // TODO: Look into middleware groups
        // Load route files
        $routesDir = $this->app->basePath('routes');
        $finder->in($routesDir)->name('*.php')->notName('index.php')->files();
        foreach ($finder as $file) {
            Route::namespace($this->namespace)
                ->domain(\home_url()) // When using route() this is used as base
                ->group($file->getRealPath());
        }
    }
}
