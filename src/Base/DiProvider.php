<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\Contract\EnvInterface;
use Ep\Base\Contract\EventListenerInterface;
use Ep\Base\Contract\InjectorInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoader;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetPublisher;
use Yiisoft\Assets\AssetPublisherInterface;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\ListenerCollection;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Log\Logger;
use Yiisoft\Log\StreamTarget;
use Yiisoft\Log\Target;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Event\ListenerCollectionFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class DiProvider implements ServiceProviderInterface
{
    public function __construct(
        private EnvInterface $env,
        private Config $config
    ) {
    }

    public function getDefinitions(): array
    {
        return [
            // Base
            ContainerInterface::class => Container::class,
            InjectorInterface::class => Injector::class,
            Env::class => $this->env,
            Config::class => $this->config,
            Aliases::class => new Aliases([
                '@root' => $this->env->getRootPath(),
                '@vendor' => $this->config->vendorPath,
                '@ep' => dirname(__DIR__, 2)
            ] + $this->config->aliases),
            // Logger
            LoggerInterface::class => [
                'class' => Logger::class,
                '__construct()' => [[Reference::to(Target::class)]]
            ],
            Target::class => StreamTarget::class,
            // Cache
            CacheInterface::class => fn (Aliases $aliases): CacheInterface => new FileCache($aliases->get($this->config->runtimeDir . '/caches')),
            YiiCacheInterface::class => Cache::class,
            // View
            AssetLoaderInterface::class => AssetLoader::class,
            AssetPublisherInterface::class => AssetPublisher::class,
            // Profiler
            ProfilerInterface::class => Profiler::class,
            // Event
            ListenerCollection::class => static fn (ContainerInterface $container, ListenerCollectionFactory $factory): ListenerCollection => $factory->create($container->has(EventListenerInterface::class) ? $container->get(EventListenerInterface::class)->getListeners() : []),
            ListenerProviderInterface::class => Provider::class,
            EventDispatcherInterface::class => Dispatcher::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
