<?php

namespace MorningMedley\Route\Classes\Rest;

use Illuminate\Container\Container;
use MorningMedley\Route\Abstracts\AbstractRouteFactory;
use MorningMedley\Route\Classes\Response;

class Router extends AbstractRouteFactory
{
    protected string $globalNamespace = 'mtwp/v1';
    protected string $exposeVar = 'mtwpRestRoutes';
    protected CallbackHandler $callbackHandler;

    public function __construct(Container $app, CallbackHandler $callbackHandler)
    {
        $this->callbackHandler = $callbackHandler;

        parent::__construct($app);

        \add_action('wp_head', [$this, 'exposeRoutes'], 2);
        \add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function __destruct()
    {
        \remove_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function current(): ?Route
    {
        return null;
    }

    public function newRoute(string $path, string|callable $callback): Route
    {
        return $this->app->makeWith(Route::class, ['path' => $path, 'callback' => $callback]);
    }

    public function newGroup(): Group
    {
        return $this->app->make(Group::class);
    }

    public function getGlobalNamespace(): string
    {
        return $this->globalNamespace;
    }

    public function setGlobalNamespace(string $globalNamespace): static
    {
        $this->globalNamespace = $globalNamespace;

        return $this;
    }

    public function addCallback(Route $route): callable
    {
        $handle = ! empty($route->getName()) ? $route->getName() : $route->getPath();
        $this->callbackHandler->addCallback($handle, $route);

        return [$this->callbackHandler, $handle];
    }

    public function exposeRoutes(): void
    {
        $exposedRoutes = $this->routes->filter(function (Route $route) {
            return $route->isExposed() && $route->getName() !== null;
        })->toArray();
        $object = [];
        foreach ($exposedRoutes as $exposedRoute) {
            $object[$exposedRoute->getName()] = $exposedRoute->getUrl();
        }
        echo "<script>var {$this->exposeVar} = " . json_encode($object) . ";</script>";
    }

    public function namespace(string $namespace): Group
    {
        return $this->newGroup()->setGroup($this->currentGroup)->namespace($namespace);
    }
}
