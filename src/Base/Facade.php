<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;

abstract class Facade
{
    protected static array $instances = [];

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return static::getInstance()->$name(...$arguments);
    }

    public static function getInstance(): object
    {
        $id = static::getFacadeAccessor();

        if (isset(static::$instances[$id])) {
            return static::$instances[$id];
        }

        return static::$instances[$id] = static::createInstance();
    }

    public static function swap(object $new): object
    {
        $old = static::getInstance();

        static::$instances[static::getFacadeAccessor()] = $new;

        return $old;
    }

    public static function clear(): void
    {
        unset(static::$instances[static::getFacadeAccessor()]);
    }

    protected static function createInstance(): object
    {
        return Ep::getDi()->get(static::getFacadeAccessor());
    }

    abstract protected static function getFacadeAccessor(): string;
}
