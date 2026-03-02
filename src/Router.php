<?php

namespace MorningMedley\Route;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Router extends \Illuminate\Routing\Router implements HttpKernelInterface
{

    public function boot()
    {
        // Set the base URL to the site's home URL
        \app('url')->useOrigin(config('route.origin', \home_url()));

        \add_action('parse_request', $this->matchRequest(...));
    }

    protected function matchRequest(\WP $environment)
    {
        $request = $this->container->make('request');
        $this->routes->refreshNameLookups();

        try {
            $route = $this->routes->match($request);
        } catch (NotFoundHttpException|MethodNotAllowedHttpException $e) {
            // This is fine. This means that WordPress should handle the request
            $this->events->dispatch(
                new RouteMatched(null, $request)
            );

            return;
        }

        if (is_a($route, Route::class)) {
            \add_action('template_redirect', $this->onTemplateRedirect(...), 9);
        }
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
