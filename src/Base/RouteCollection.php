<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Facade\Route;
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
            $fetch = static function (RouteCollector|RouteGroup $route) use (&$fetch): array {
                if ($route instanceof RouteCollector) {
                    $name = $route->getName();
                    if ($name === null) {
                        return [];
                    } else {
                        return [
                            [
                                'name' => $name,
                                'rule' => $route->getRule()
                            ]
                        ];
                    }
                } elseif ($route instanceof RouteGroup) {
                    $name = $route->getName();
                    $prefix = $route->getPrefix();
                    $result = [];
                    foreach ($route->getRoutes() as $r) {
                        foreach ($fetch($r) as $item) {
                            $item['rule'][1] = $prefix . $item['rule'][1];
                            $result[] = [
                                'name' => $name . $item['name'],
                                'rule' => $item['rule']
                            ];
                        }
                    }
                    return $result;
                }
            };
            foreach ($this->routes as $route) {
                foreach ($fetch($route) as $item) {
                    $this->names[$item['name']] = $item['rule'];
                }
            }
        }
        return $this->names;
    }

    private ?string $name = null;

    public function name(string $name): RouteCollection
    {
        $this->name = $name;
        return $this;
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

        if ($this->name !== null) {
            $route->name($this->name);
            $this->name = null;
        }

        array_push($this->routes, $route);

        return $route;
    }

    public function group(string $prefix, Closure $callback): RouteGroup
    {
        $group = new RouteGroup($prefix, $callback);

        if ($this->name !== null) {
            $group->name($this->name);
            $this->name = null;
        }

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

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getRoutes(): array
    {
        $old = Route::swap(new RouteCollection());

        call_user_func($this->callback);

        return Route::swap($old)->getRoutes();
    }
}
