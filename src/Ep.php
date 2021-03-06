<?php

declare(strict_types=1);

use Ep\Base\Core;
use Ep\Base\Facade;

/**
 * @method static Core create(string $rootPath)
 * @method static \Yiisoft\Di\ContainerConfig getContainerConfig()
 * @method static \Ep\Base\Contract\EnvInterface getEnv()
 * @method static \Ep\Base\Config getConfig()
 * @method static \Psr\Container\ContainerInterface getDi()
 * @method static \Yiisoft\Factory\Factory getFactory()
 * @method static \Ep\Base\Contract\InjectorInterface getInjector()
 * @method static \Yiisoft\Db\Connection\Connection getDb(string $id = null)
 * @method static \Psr\SimpleCache\CacheInterface getCache(string $id = null)
 * @method static \Psr\Log\LoggerInterface getLogger(string $id = null)
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
    protected static function createInstance(): object
    {
        return new Core();
    }
}
