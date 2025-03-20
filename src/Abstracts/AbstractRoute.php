<?php

namespace MorningMedley\Route\Abstracts;

use Illuminate\Container\Container;
use Illuminate\Http\Response;
use MorningMedley\Application\Http\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property Container $app
 */
abstract class AbstractRoute
{
    protected Container $app;
    protected string $path;
    protected $callback;
    protected ?string $name = null;
    protected array $requestMethods = [];

    protected ?AbstractGroup $group = null;

    abstract public function register(): void;

    abstract public function getUrl(array $args = []): string;

    /**
     * Route constructor.
     *
     * @param  string  $path
     * @param  callable|string  $callback
     */
    public function __construct(
        Container $app,
        string $path,
        callable|string|array $callback
    ) {
        $this->app = $app;
        $this->path = trim($path, '/');
        $this->callback = $callback;

        return $this;
    }

    public function setGroup(?AbstractGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get the path / relative URL
     *
     * @return string
     */
    public function getPath(): string
    {
        $prefix = (string) $this->group?->getPrefix();

        return ! empty($prefix) ? implode('/', [$prefix, $this->path]) : $this->path;
    }

    /**
     * Get the callback
     *
     * @return callable|string
     */
    public function getCallback(): callable|string|array
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
        return $this->requestMethods;
    }

    /**
     * Set the allowed Request Methods
     *
     * @param  array  $methods  Array of string such as "GET", "POST", "PUT" or "any"
     *
     * @return $this
     */
    public function setRequestMethods(array $methods): static
    {
        $this->requestMethods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * Calls the route callback
     */
    public function call(Request $request, ...$args): static
    {
        // If callback is a string and a class, then it must be for invoking
        $callback = $this->getCallback();
        if (is_string($callback) && class_exists($callback)) {
            $callback = app($callback);
        }

        try{
            $response = app()->call($callback);
        }catch (\Error $e){
            // This indicates that the method is not static.
            // So we construct an instance of the controller class
            if (is_array($callback) && is_string($callback[0])) {
                $callback[0] = app($callback[0]);
            }

            $response = app()->call($callback);
        }

        if(empty($response)){
            return $this;
        }

        if(!is_a($response, Response::class)){
            $rf = new ResponseFactory(app('view'));
            $rf->make($response)->send();
        }else{
            $response->send();
        }

        return $this;
    }

    /**
     * Gets the name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name of the route
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the routes associated group if it has one
     *
     * @return AbstractGroup|null
     */
    public function getGroup(): ?AbstractGroup
    {
        return $this->group;
    }

    /**
     * Handle all middleware for this route
     *
     * @param  Request  $request
     *
     * @return $this
     */
    public function handleMiddleware(Request $request): self
    {
        $this->getGroup()?->handleMiddleware($request);

        return $this;
    }
}
