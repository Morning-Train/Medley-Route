<?php

namespace MorningMedley\Route\Classes\Rewrite;

use Illuminate\Container\Container;
use MorningMedley\Route\Abstracts\AbstractRouteFactory;
use MorningMedley\Route\Classes\Response;
use Symfony\Component\HttpFoundation\Request;

class Router extends AbstractRouteFactory
{
    protected string $routeQueryVar = 'mtwp_route';
    protected string $hashOption = 'mtwp_route_hash';
    protected ?Route $matchedRoute = null;

    public function __construct(Container $app)
    {
        parent::__construct($app);
        \add_action('init', [$this, 'registerRoutes']);
    }

    public function __destruct()
    {
        \remove_action('init', [$this, 'registerRoutes']);
        \remove_action('parse_request', [$this, 'matchRequest']);
        \remove_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    public function current(): ?Route
    {
        return $this->matchedRoute;
    }

    public function registerRoutes()
    {
        $this->addMainRewriteTag();
        parent::registerRoutes();
        $routesHash = md5(serialize($this->routes->toArray()));
        if ($routesHash != get_option($this->hashOption)) {
            \flush_rewrite_rules();
            update_option($this->hashOption, $routesHash);
        }
        \add_action('parse_request', [$this, 'matchRequest']);
    }

    public function newRoute(string $path, $callback): Route
    {
        return $this->app->makeWith(Route::class, ['path' => $path, 'callback' => $callback]);
    }

    public function newGroup(): Group
    {
        return $this->app->make(Group::class);
    }

    public function addMainRewriteTag(): void
    {
        \add_rewrite_tag('%' . $this->routeQueryVar . '%', '([^/]+)');
    }

    public function getQueryVar(): string
    {
        return $this->routeQueryVar;
    }

    public function matchRequest(\WP $environment): void
    {
        if (empty($environment->query_vars[$this->routeQueryVar])) {
            return;
        }

        $matchedRoute = $this->getRouteByQueryVars($environment->query_vars);

        if ($matchedRoute instanceof Route) {
            $this->matchedRoute = $matchedRoute;
        }

        \add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    protected function getRouteByQueryVars(array $query_vars): ?Route
    {
        $path = \urlencode($query_vars[$this->routeQueryVar]);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        return $this->getRouteByPathAndMethod($path, $requestMethod);
    }

    public function getRouteByPathAndMethod(string $path, string $requestMethod): ?Route
    {
        $matchedRoutes = $this->routes->filter(function (Route $route) use ($requestMethod, $path) {
            if (\urlencode($route->getPath()) === $path) {
                if ($requestMethod === null || in_array($requestMethod, $route->getRequestMethods())) {
                    return true;
                }
            }

            return false;
        });

        return $matchedRoutes->first();
    }

    public function onTemplateRedirect()
    {
        if (! $this->matchedRoute instanceof Route) {
            $this->app->make(Response::class)->withWordPressTemplate('404', 404)->send();
            exit;
        }

        $request = app(Request::class);
        $this->matchedRoute->handleMiddleware($request)->call($request);
        exit;
    }

}
