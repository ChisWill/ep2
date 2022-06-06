<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\Route;
use Ep\Exception\NotFoundException;
use Ep\Helper\Str;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector as FastRouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Psr\SimpleCache\CacheInterface;
use Attribute;
use Closure;

use function FastRoute\cachedDispatcher;

final class Router
{
    private array $collectionRules = [];
    private array $attributeRules = [];

    public function __construct(
        private Config $config,
        private Aliases $aliases,
        private CacheInterface $cache,
        private RouteCollection $routeCollection
    ) {
        $this->initCollection();
        $this->initAttribute();
    }

    private bool $enableDefaultRule = true;

    public function withEnableDefaultRule(bool $enableDefaultRule): self
    {
        $new = clone $this;
        $new->enableDefaultRule = $enableDefaultRule;
        return $new;
    }

    private array $defaultRule = [
        Method::ALL,
        '{prefix:[\w/-]*?}{controller:/?[a-zA-Z][\w-]*|}{action:/?[a-zA-Z][\w-]*|}',
        '<prefix>/<controller>/<action>'
    ];

    public function withDefaultRule(?array $rule): self
    {
        if ($rule === null) {
            return $this;
        }
        $new = clone $this;
        $new->defaultRule = $rule;
        return $new;
    }

    /**
     * @throws NotFoundException
     */
    public function match(string $path, string $method = Method::GET): array
    {
        return $this->solveRouteResult(
            cachedDispatcher(function (FastRouteCollector $route): void {
                $this->addCollectionRoute($route);

                $this->addAttributeRoute($route);

                if ($this->enableDefaultRule) {
                    $route->addRoute(...$this->defaultRule);
                }
            }, [
                'cacheFile' => $this->aliases->get($this->config->runtimeDir . '/route.cache'),
                'cacheDisabled' => $this->config->debug
            ])
                ->dispatch($method, rtrim($path, '/') ?: '/')
        );
    }

    /**
     * @throws NotFoundException
     */
    private function solveRouteResult(array $routeResult): array
    {
        switch ($routeResult[0]) {
            case Dispatcher::FOUND:
                return $this->replaceHandler($routeResult[1], $routeResult[2]);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return [false, null, null];
            default:
                throw new NotFoundException('Page is not found.');
        }
    }

    private function replaceHandler(string|array|Closure $handler, array $params): array
    {
        if (is_string($handler)) {
            preg_match_all('/<(\w+)>/', $handler, $matches);
            $match = array_flip($matches[1]);
            $intersect = array_intersect_key($params, $match);
            $params = array_diff_key($params, $match);
            $captureParams = [];
            foreach ($intersect as $key => &$value) {
                $value = strtolower($value);
                $captureParams['<' . $key . '>'] = trim($value, '/');
            }
            $handler = strtr($handler, $captureParams);
        }
        return [true, $handler, $params];
    }

    private function addAttributeRoute(FastRouteCollector $route): void
    {
        foreach ($this->attributeRules as $path => [$method, $handler]) {
            $route->addRoute($method, $path, $handler);
        };
    }

    private function addCollectionRoute(FastRouteCollector $route): void
    {
        $add = static function (string|int $group, array $rules) use (&$add, $route): void {
            if (is_string($group)) {
                $route->addGroup($group, static function () use ($add, $rules): void {
                    foreach ($rules as $group => $rule) {
                        $add($group, $rule);
                    }
                });
            } else {
                $route->addRoute(...$rules);
            }
        };
        foreach ($this->collectionRules as $group => $rule) {
            $add($group, $rule);
        };
    }

    private function initAttribute(): void
    {
        foreach ($this->cache->get(Constant::CACHE_ATTRIBUTE_DATA)[Route::class] ?? [] as $class => $value) {
            if (!isset($value[Attribute::TARGET_METHOD])) {
                continue;
            }

            if (isset($value[Attribute::TARGET_CLASS])) {
                $path = '/' . trim($value[Attribute::TARGET_CLASS]['path'], '/');
                $method = $value[Attribute::TARGET_CLASS]['method'] ?? Method::GET;
            } else {
                $path = '';
                $method = Method::GET;
            }

            foreach ($value[Attribute::TARGET_METHOD] as $item) {
                $this->attributeRules[sprintf('%s/%s', $path, trim($item['path'], '/'))] = [
                    (array) ($item['method'] ?? $method),
                    [$class, Str::rtrim($item['target'], $this->config->actionSuffix)]
                ];
            }
        }
    }

    private function initCollection(): void
    {
        $fetch = static function (RouteCollector|RouteGroup $route, array &$list) use (&$fetch): void {
            if ($route instanceof RouteCollector) {
                $list[] = $route->getRule();
            } elseif ($route instanceof RouteGroup) {
                $list[$route->getPrefix()] = [];
                foreach ($route->getRoutes() as $r) {
                    $fetch($r, $list[$route->getPrefix()]);
                }
            }
        };
        foreach ($this->routeCollection->getRoutes() as $route) {
            $fetch($route, $this->collectionRules);
        }
    }
}
