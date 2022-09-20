<?php

namespace Morningtrain\WP\Route;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Route\Classes\RouteService;
use Morningtrain\WP\Route\Classes\Route as RouteInstance;

class Route
{
    public static function loadDir(string|array $path)
    {
        Loader::create($path);
        RouteService::setup();
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function match(array $requestMethods, string $path, callable $callback): RouteInstance
    {
        $route = new RouteInstance($path, $callback);

        $route->setRequestMethods(
            $requestMethods
        );
        RouteService::addRoute($route);

        return $route;
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function any(string $path, callable $callback): RouteInstance
    {
        return static::match([], $path, $callback);
    }

    /**
     * Register a HTTP GET request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function get(string $path, callable $callback): RouteInstance
    {
        return static::match(['GET'], $path, $callback);
    }

    /**
     * Register a HTTP POST request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function post(string $path, callable $callback): RouteInstance
    {
        return static::match(['POST'], $path, $callback);
    }

    /**
     * Register a HTTP PUT request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function put(string $path, callable $callback): RouteInstance
    {
        return static::match(['PUT'], $path, $callback);
    }

    /**
     * Register a HTTP PATCH request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function patch(string $path, callable $callback): RouteInstance
    {
        return static::match(['PATCH'], $path, $callback);
    }

    /**
     * Register a HTTP DELETE request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function delete(string $path, callable $callback): RouteInstance
    {
        return static::match(['DELETE'], $path, $callback);
    }

    /**
     * Register a HTTP OPTIONS request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return RouteInstance
     */
    public static function options(string $path, callable $callback): RouteInstance
    {
        return static::match(['OPTIONS'], $path, $callback);
    }

    /**
     * Gets a defined route by name
     * Wrapper to allow access through this class
     *
     * @param  string  $name
     * @return RouteInstance
     */
    public static function exists(string $name): RouteInstance
    {
        return RouteService::exists($name);
    }

    /**
     * Returns the URL of a named route
     * Wrapper to allow access through this class
     *
     * @param  string  $name
     * @param  array  $args
     *
     * @return string|null
     */
    public static function route(string $name, array $args = []): ?string
    {
        return RouteService::getUrl($name, $args);
    }

    /**
     * Checks if a route is currently matched
     *
     * @param  string  $name
     *
     * @return bool
     * @see RouteService::isCurrentRoute
     */
    public static function is(string $name): bool
    {
        return RouteService::isCurrentRoute($name);
    }

    /**
     * Returns the currently matched route
     *
     * @return RouteInstance|null
     * @see RouteService::currentRoute
     */
    public static function current(): ?RouteInstance
    {
        return RouteService::currentRoute();
    }
}
