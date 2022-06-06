<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use InvalidArgumentException;

abstract class Facade
{
    private static array $instances = [];

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::getInstance()->$name(...$arguments);
    }

    public static function swap(object $new): object
    {
        $old = self::getInstance();

        self::$instances[static::getFacadeAccessor()] = $new;

        return $old;
    }

    public static function clear(): void
    {
        unset(self::$instances[static::getFacadeAccessor()]);
    }

    protected static function getFacadeAccessor(): string
    {
        throw new InvalidArgumentException(sprintf('%s does not implement method %s().', static::class, __FUNCTION__));
    }

    public static function getInstance(): object
    {
        $id = static::getFacadeAccessor();

        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        return self::$instances[$id] = Ep::getDi()->get($id);
    }
}
