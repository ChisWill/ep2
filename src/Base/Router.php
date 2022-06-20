<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\Route;
use Ep\Exception\PageNotFoundException;
use Ep\Kit\Annotate;
use Ep\Helper\Str;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector as FastRouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Yiisoft\Http\Method;
use Psr\SimpleCache\CacheInterface;
use Attribute;
use Closure;

use function FastRoute\cachedDispatcher;

final class Router
{
    private string $suffix;
    private array $collectionRules = [];
    private array $attributeRules = [];

    public function __construct(
        private Config $config,
        private Aliases $aliases,
        private CacheInterface $cache,
        private RouteCollection $routeCollection,
        private Annotate $annotate
    ) {
        $this->suffix = $config->controllerSuffix;

        $this->initCollection();
        $this->initAttribute();
    }

    public function withSuffix(string $suffix): self
    {
        $new = clone $this;
        $new->suffix = $suffix;
        return $new;
    }

    private bool $enableDefaultRule = true;

    public function withEnableDefaultRule(bool $enable): self
    {
        $new = clone $this;
        $new->enableDefaultRule = $enable;
        return $new;
    }

    private bool $enableAttributeRule = true;

    public function withEnableAttributeRule(bool $enable): self
    {
        $new = clone $this;
        $new->enableAttributeRule = $enable;
        return $new;
    }

    private bool $enableCollectioneRule = true;

    public function withEnableCollectioneRule(bool $enable): self
    {
        $new = clone $this;
        $new->enableCollectioneRule = $enable;
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
     * @throws PageNotFoundException
     */
    public function match(string $path, string $method = Method::GET): array
    {
        return $this->solve(
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
     * @throws PageNotFoundException
     */
    private function solve(array $result): array
    {
        switch ($result[0]) {
            case Dispatcher::FOUND:
                return $this->replaceHandler($result[1], $result[2]);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return [false, null, null];
            default:
                throw new PageNotFoundException('Page is not found.');
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
        if (!$this->enableAttributeRule) {
            return;
        }

        foreach ($this->attributeRules as $path => [$method, $handler]) {
            $route->addRoute($method, $path, $handler);
        };
    }

    private function addCollectionRoute(FastRouteCollector $route): void
    {
        if (!$this->enableCollectioneRule) {
            return;
        }

        $add = static function (array $groupRules) use (&$add, $route): void {
            foreach ($groupRules as $group => $rules) {
                if (is_string($group)) {
                    $route->addGroup($group, static function () use ($add, $rules): void {
                        $add($rules);
                    });
                } else {
                    $route->addRoute(...$rules);
                }
            }
        };
        $add($this->collectionRules);
    }

    private function initAttribute(): void
    {
        foreach ($this->annotate->getCache(Route::class) as $class => $value) {
            if (isset($value[Attribute::TARGET_CLASS])) {
                $path = trim($value[Attribute::TARGET_CLASS]['path'], '/');
                $path = $path ? '/' . $path : '';
                $method = $value[Attribute::TARGET_CLASS]['method'] ?? Method::ALL;
            } else {
                $path = '';
                $method = Method::ALL;
            }

            foreach ($value[Attribute::TARGET_METHOD] ?? [] as $item) {
                $this->attributeRules[sprintf('%s/%s', $path, trim($item['path'], '/'))] = [
                    (array) ($item['method'] ?? $method),
                    [$class, $item[Constant::ATTRIBUTE_TARGET]]
                ];
            }

            if ($path) {
                [$path, $handler] = $this->generateRuleByClass($path, $class);
                $this->attributeRules[$path] = [$method, $handler];
            }
        }
    }

    private function initCollection(): void
    {
        $dir = $this->aliases->get($this->config->routeDir);
        if (!file_exists($dir)) {
            return;
        }

        foreach (FileHelper::findFiles($dir, [
            'filter' => (new PathMatcher())->only('**.php')
        ]) as $file) {
            require($file);
        }

        $fetch = static function (array $routes, array &$result) use (&$fetch): void {
            foreach ($routes as $route) {
                if ($route instanceof RouteCollector) {
                    $result[] = $route->getRule();
                } elseif ($route instanceof RouteGroup) {
                    $result[$route->getPrefix()] = [];
                    $fetch($route->getRoutes(), $result[$route->getPrefix()]);
                }
            }
        };
        $fetch($this->routeCollection->getRoutes(), $this->collectionRules);
    }

    private function generateRuleByClass(string $path, string $class): array
    {
        return [
            rtrim($path, '/') . '/{action:[a-zA-Z][\w-]*}',
            implode(
                '/',
                array_map(
                    static fn ($item): string => Str::camelToId($item, '-'),
                    array_filter(
                        explode(
                            '\\',
                            Str::ltrim(
                                str_replace($this->suffix, '', $class),
                                $this->config->rootNamespace
                            )
                        )
                    )
                )
            ) . '/<action>'
        ];
    }
}
