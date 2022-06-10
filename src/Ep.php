<?php

declare(strict_types=1);

use Ep\Base\Core;
use Ep\Base\Facade;

/**
 * @method static Core create(string $rootPath)
 * @method static \Yiisoft\Di\ContainerConfig getContainerConfig()
 * @method static \Ep\Contract\EnvInterface getEnv()
 * @method static \Ep\Base\Config getConfig()
 * @method static \Psr\Container\ContainerInterface getDi()
 * @method static \Yiisoft\Factory\Factory getFactory()
 * @method static \Ep\Contract\InjectorInterface getInjector()
 * @method static \Yiisoft\Db\Connection\Connection getDb(string $id = null)
 * @method static \Psr\SimpleCache\CacheInterface getCache(string $id = null)
 * @method static \Psr\Log\LoggerInterface getLogger(string $id = null)
 * @method static void scan()
 * @method static bool isSelf(string $rootNamespace = null)
 */
final class Ep extends Facade
{
    public const VERSION = '2.0';

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return Core::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function create(): object
    {
        return new Core();
    }
}
