<?php

namespace MorningMedley\Route;

use Illuminate\Filesystem\Filesystem;
use MorningMedley\Facades\Rest as RestFacade;
use MorningMedley\Route\Classes\Rest\CallbackHandler;
use MorningMedley\Route\Classes\Rest\Router as RestRouter;
use MorningMedley\Route\Classes\Rewrite\Router as RewriteRouter;
use MorningMedley\Facades\Route as RouteFacade;
use Symfony\Component\Finder\SplFileInfo;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/config.php", 'route');

        RouteFacade::setFacadeApplication($this->app);
        RestFacade::setFacadeApplication($this->app);
        $this->app->singleton('rewrite-router', RewriteRouter::class);
        $this->app->singleton('rest-router', RestRouter::class);

    }

    public function boot()
    {
        $cache = $this->app->makeWith('file.cache', ['namespace' => 'route', 'defaultLifetime' => DAY_IN_SECONDS]);

        $files = $cache->get('route', function () {
            $files = [];
            foreach ((array) $this->app['config']->get('route.paths') as $path) {
                $path = $this->app->basePath($path);
                $filesystem = $this->app->make(Filesystem::class);
                /** @var Filesystem $filesystem */
                $files = [
                    ...$files,
                    ...array_map(fn(SplFileInfo $file) => $file->getRealPath(), $filesystem->files($path)),
                ];
            }

            return $files;
        });
        foreach ($files as $file) {
            require_once $file;
        }
    }
}
