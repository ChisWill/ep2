<?php

declare(strict_types=1);

use Ep\Base\Core;
use Ep\Base\Facade;

/**
 * @method Core create(string $rootPath, string $configPath)
 */
final class Ep extends Facade
{
    public const VERSION = '2.0';

    protected static function getFacadeAccessor(): string
    {
        return Core::class;
    }

    protected static function create(): object
    {
        return new Core();
    }
}
