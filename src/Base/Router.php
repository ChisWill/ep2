<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\Route;
use Ep\Exception\NotFoundException;
use Ep\Helper\Str;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Psr\SimpleCache\CacheInterface;
use Attribute;

use function FastRoute\cachedDispatcher;

final class Router
{
    private array $attributeRules = [];

    public function __construct(
        private Config $config,
        private Aliases $aliases,
        private CacheInterface $cache
    ) {
        $this->bootstrap();
    }

    private function bootstrap(): void
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
            cachedDispatcher(function (RouteCollector $route): void {
                // if ($this->rule) {
                //     $route->addGroup('', $this->rule);
                // }

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

    private function replaceHandler(string|array $handler, array $params): array
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

    private function addAttributeRoute(RouteCollector $route): void
    {
        foreach ($this->attributeRules as $path => [$method, $handler]) {
            $route->addRoute($method, $path, $handler);
        };
    }
}
