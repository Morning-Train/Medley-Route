<?php

namespace MorningMedley\Route;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class Router extends \Illuminate\Routing\Router
{

    public function boot()
    {
        \add_action('init', $this->init(...), 1);
    }

    public function init()
    {
        $this->addRewriteTag();
        $this->routes->refreshNameLookups();
        $routesHash = md5(json_encode($this->routes->getRoutes()));
        if ($routesHash != get_option($this->hashOption())) {
            // Routes have changed.
            // Add new rewrite rules
            $this->addRewriteRules();
            // Flush rules
            \flush_rewrite_rules();
            \update_option($this->hashOption(), $routesHash);
        }

        \add_action('parse_request', $this->matchRequest(...));
    }

    public function addRewriteTag()
    {
        \add_rewrite_tag('%' . $this->routeQueryVar() . '%', '([^/]+)');
    }

    public function routeQueryVar(): string
    {
        return $this->container->make('config')->get('route.query_var');
    }

    protected function hashOption(): string
    {
        return $this->container->make('config')->get('route.hash_option');
    }

    protected function addRewriteRules()
    {
        foreach ($this->getRoutes() as $route) {
            $this->addRewriteRule($route);
        }
    }

    protected function addRewriteRule(Route $route)
    {
        // TODO: Clean this up!
        $path = $this->routeQueryVar() . "=" . \urlencode($route->uri());
        if ($route->hasParameters()) {
            foreach ($route->parameterNames() as $param) {
                $path .= "&{$param}=\$matches[{$i}]";
                \add_rewrite_tag('%' . $param . '%', '([^/]+)');
                $i++;
            }

        }

        $reqex = '^' . ltrim(
                trim(
                    str_replace(
                        array_map(fn(string $paramName) => "{" . $paramName . "}", $route->parameterNames()),
                        '([^/]+)',
                        $route->uri())),
                '/'
            ) . '$';
        $query = 'index.php?' . $path;
        $position = 'top';

        \add_rewrite_rule(
            $reqex,
            $query,
            $position
        );
    }

    protected function matchRequest(\WP $environment)
    {
        if (empty($environment->query_vars[$this->routeQueryVar()])) {
            return;
        }

        \add_action('template_redirect', $this->onTemplateRedirect(...));
    }

    public function onTemplateRedirect(): never
    {
        // Let the router handle it from here
        $request = $this->container->make('request');
        $this->container->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
        exit;
    }
}
