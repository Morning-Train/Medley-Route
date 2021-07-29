<?php


namespace Morningtrain\WP\Router;


class Route
{
    private string $path;
    private array $request_methods = [];
    private string $position = 'top';
    private $callback;

    private array $customParamRegexes = [];
    private string $defaultParamRegex = '([^/]+)';
    private array $params = [];

    /**
     * Route constructor.
     *
     * @param string $path
     * @param callable $callback
     */
    public function __construct(string $path, callable $callback)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->extractPathParams();
    }

    private function extractPathParams()
    {
        $params = [];
        \preg_match_all("/{\w+}/", $this->getPath(), $params);

        $this->params = array_map(
            function ($p) {
                return trim($p, '{}');
            },
            $params[0]
        );
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get the path / relative URL
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the callback
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * Get list of valid request methods
     * Array is empty if all (any) are allowed
     *
     * @return array
     */
    public function getRequestMethods(): array
    {
        return $this->request_methods;
    }

    /**
     * Set the allowed Requst Methods
     *
     * @param array $methods Array of string such as "GET", "POST", "PUT" or "any"
     *
     * @return $this
     */
    public function setRequestMethods(array $methods): Route
    {
        if ($methods === ['any']) {
            $methods = [];
        }

        $this->request_methods = $methods;

        return $this;
    }

    /**
     * Get position
     * Value can be "top" or "bottom"
     *
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Set the rewrite position
     *
     * @param string $position
     *
     * @return string
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_rule/
     */
    public function setPosition(string $position)
    {
        return $this->position = $position;
    }

    /**
     * Calls the route callback
     */
    public function call()
    {
        $callback = $this->getCallback();
        if (!is_callable($callback)) {
            return;
        }
        $callback(...array_values($this->getQueryVars()));
    }

    /**
     * Returns an associative array of the route query vars
     *
     * @return array
     */
    public function getQueryVars(): array
    {
        return array_combine(
            $this->getParams(),
            array_map(
                function ($p) {
                    return \get_query_var($p);
                },
                $this->getParams()
            )
        );
    }

    public function getParamRegex(string $param)
    {
        if (!key_exists($param, $this->customParamRegexes)) {
            return $this->defaultParamRegex;
        }

        return $this->customParamRegexes[$param];
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function match(array $requestMethods, string $path, callable $callback): Route
    {
        $route = new static($path, $callback);
        $route->setRequestMethods(
            $requestMethods
        ); // TODO: Filter these. But what to do if an invalid method is present? If removed with array_filter then array will be empty and any request type will pass through
        RouteService::addRoute($route);
        return $route;
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function any(string $path, callable $callback): Route
    {
        return static::match([], $path, $callback);
    }

    /**
     * Register a HTTP GET request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function get(string $path, callable $callback): Route
    {
        return static::match(['GET'], $path, $callback);
    }

    /**
     * Register a HTTP POST request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function post(string $path, callable $callback): Route
    {
        return static::match(['POST'], $path, $callback);
    }

    /**
     * Register a HTTP PUT request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function put(string $path, callable $callback): Route
    {
        return static::match(['PUT'], $path, $callback);
    }

    /**
     * Register a HTTP PATCH request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function patch(string $path, callable $callback): Route
    {
        return static::match(['PATCH'], $path, $callback);
    }

    /**
     * Register a HTTP DELETE request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function delete(string $path, callable $callback): Route
    {
        return static::match(['DELETE'], $path, $callback);
    }

    /**
     * Register a HTTP OPTIONS request Route
     *
     * @param string $path
     * @param callable $callback
     *
     * @return Route
     */
    public static function options(string $path, callable $callback): Route
    {
        return static::match(['OPTIONS'], $path, $callback);
    }
}