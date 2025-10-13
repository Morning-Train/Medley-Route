<?php

namespace MorningMedley\Route;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Router extends \Illuminate\Routing\Router implements HttpKernelInterface
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

    /**
     * Add necessary rewrite tags and rule
     *
     * @param  Route  $route
     * @return void
     */
    protected function addRewriteRule(Route $route)
    {
        $path = $this->routeQueryVar() . "=" . \urlencode($route->uri());

        $rewriteParameters = $route->rewriteParameters();
        $regexedParams = [];
        // If there are parameters then add rewrite tags for these and prepare the regex for rewrite rule
        if (! empty($rewriteParameters)) {
            $i = 0;
            foreach ($rewriteParameters as $parameter) {
                // Add the parameter's regex
                $key = $parameter['name'] . ($parameter['optional'] ? '?' : '');
                $regexedParams[$key] = $parameter['regex'];

                // Append it to the rewrite paths
                $path .= "&{$parameter['name']}=\$matches[{$i}]";

                // Add rewrite tag for this parameter so that it is available through query_vars
                \add_rewrite_tag('%' . $parameter['name'] . '%', $parameter['regex']);
                $i++;
            }
        }

        // The full route regex
        $reqex = '^' . ltrim(
                trim(
                    str_replace(
                        array_map(fn(string $paramName) => "{" . $paramName . "}", array_keys($regexedParams)),
                        array_values($regexedParams),
                        $route->uri())),
                '/'
            ) . '$';
        $query = 'index.php?' . $path;
        $position = 'top';

        // Add the rule
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
        $this->handleRequest($request)->send();
        exit;
    }

    public function handle(SymfonyRequest $request, int $type = self::MAIN_REQUEST, bool $catch = true): SymfonyResponse
    {
        return $this->handleRequest(Request::createFromBase($request));
    }

    public function handleRequest($request)
    {
        return $this->container->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
    }

    public function routesAreCached()
    {
        return $this->container->make('files')->exists($this->getCachedRoutesPath());
    }

    public function getCachedRoutesPath()
    {
        return $this->container->bootstrapPath('cache/route.php');
    }
}
