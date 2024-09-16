<?php

namespace MorningMedley\Route;

use Illuminate\Filesystem\Filesystem;
use MorningMedley\Facades\Rest as RestFacade;
use MorningMedley\Route\Classes\Rest\CallbackHandler;
use MorningMedley\Route\Classes\Rest\Router as RestRouter;
use MorningMedley\Route\Classes\Rewrite\Router as RewriteRouter;
use MorningMedley\Facades\Route as RouteFacade;
use Symfony\Component\Finder\SplFileInfo;

class RouteServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/config.php", 'route');

        RouteFacade::setFacadeApplication($this->app);
        $this->app->singleton('rewrite-router', RewriteRouter::class);
    }

    public function boot()
    {
        if (! $this->app->configurationIsCached()) {
            $files = [];
            foreach ($this->app['config']->get('route.paths') as $path) {
                if (! is_dir($path)) {
                    $path = $this->app->basePath($path);
                    if (! is_dir($path)) {
                        continue;
                    }
                }
                $filesystem = $this->app->make(Filesystem::class);
                /** @var Filesystem $filesystem */
                $files = [
                    ...$files,
                    ...array_map(fn(SplFileInfo $file) => $file->getRealPath(), $filesystem->files($path)),
                ];
            }
            $this->app['config']->set('route.files', $files);
        }

        foreach ($this->app['config']->get('route.files') as $file) {
            require_once $file;
        }
    }
}
