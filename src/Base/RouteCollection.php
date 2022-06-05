<?php

declare(strict_types=1);

namespace Ep\Base;

use Yiisoft\Http\Method;
use Closure;

final class RouteCollection
{
    private array $routes = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private ?array $names = null;

    public function getNames(): array
    {
        if ($this->names === null) {
            $this->names = [];
            $fetch = static function (RouteCollector|RouteGroup $route) use (&$fetch) {
                if ($route instanceof RouteCollector) {
                    $name = $route->getName();
                    if ($name !== null) {
                        return [$name => $route->getRule()];
                    } else {
                        return null;
                    }
                } elseif ($route instanceof RouteGroup) {
                    $prefix = $route->getName();
                    $list = [];
                    foreach ($route->getRoute(new RouteCollection()) as $item) {
                        $result = $fetch($item);
                        if ($result !== null) {
                            if (is_string(key($result))) {
                                $list += $result;
                            } else {
                                foreach ($result as $r) {
                                    $list += $r;
                                }
                            }
                        }
                    }
                    return $list ?: null;
                }
            };
            foreach ($this->routes as $route) {
                $result = $fetch($route);
                if ($result !== null) {
                    if (is_string(key($result))) {
                        $this->names += $result;
                    } else {
                        foreach ($result as $r) {
                            $this->names += $r;
                        }
                    }
                }
            }
        }
        return $this->names;
    }

    public function get(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::GET, $pattern, $action);
    }

    public function post(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::POST, $pattern, $action);
    }

    public function put(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::PUT, $pattern, $action);
    }

    public function delete(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::DELETE, $pattern, $action);
    }

    public function patch(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::PATCH, $pattern, $action);
    }

    public function head(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::HEAD, $pattern, $action);
    }

    public function options(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::OPTIONS, $pattern, $action);
    }

    public function any(string $pattern, string|array|Closure $action): RouteCollector
    {
        return $this->match(Method::ALL, $pattern, $action);
    }

    public function match(string|array $method, string $pattern, string|array|Closure $action): RouteCollector
    {
        $route = new RouteCollector($method, $pattern, $action);
        array_push($this->routes, $route);
        return $route;
    }

    public function group(string $prefix, Closure $callback): RouteGroup
    {
        $group = new RouteGroup($prefix, $callback);
        array_push($this->routes, $group);
        return $group;
    }
}

final class RouteCollector
{
    public function __construct(
        private string|array $method,
        private string $pattern,
        private string|array|Closure $action
    ) {
        $this->action = $action;
    }

    private ?string $name = null;

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRule(): array
    {
        return [
            $this->method,
            $this->pattern,
            $this->action
        ];
    }
}

final class RouteGroup
{
    public function __construct(
        private string $prefix,
        private Closure $callback
    ) {
    }

    private ?string $name = null;

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRoute(RouteCollection $route): array
    {
        call_user_func($this->callback, $route);
        return $route->getRoutes();
    }
}
