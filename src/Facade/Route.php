<?php

declare(strict_types=1);

namespace Ep\Facade;

use Ep\Base\Facade;
use Ep\Base\RouteCollection;

/**
 * @method static \Ep\Base\RouteCollector get(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector post(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector put(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector delete(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector patch(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector options(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector any(string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteCollector match(string|array $method, string $pattern, \callable|string $action)
 * @method static \Ep\Base\RouteGroup group(string $prefix, \Closure $callback)
 */
final class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RouteCollection::class;
    }
}
