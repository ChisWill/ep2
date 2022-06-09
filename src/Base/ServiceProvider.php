<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\Config;
use Ep\Base\Container;
use Ep\Base\Env;
use Ep\Base\Injector;
use Ep\Console\Application as ConsoleApplication;
use Ep\Console\CommandLoader;
use Ep\Console\EventDispatcher;
use Ep\Console\Factory as ConsoleFactory;
use Ep\Contract\ConsoleFactoryInterface;
use Ep\Contract\EnvInterface;
use Ep\Contract\InjectorInterface;
use Ep\Tests\Support\Car\Car;
use Ep\Tests\Support\Car\CarInterface;
use Ep\Web\Application;
use Ep\Web\NotFoundHandler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoader;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetManager;
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
use Yiisoft\Log\Target;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Yii\Event\ListenerCollectionFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class ServiceProvider implements ServiceProviderInterface
{
    private Config $config;

    public function __construct(private EnvInterface $env)
    {
        $this->config = $env->getConfig();
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
                '@ep' => dirname(__DIR__)
            ] + $this->config->aliases),
            // Web Application
            Application::class => [
                '__construct()' => [
                    'notFoundHandler' => Reference::to(NotFoundHandler::class)
                ]
            ],
            // Console
            ConsoleFactoryInterface::class => ConsoleFactory::class,
            ConsoleApplication::class => [
                'setAutoExit()' => [false],
                'setCommandLoader()' => [Reference::to(CommandLoaderInterface::class)],
                'setDispatcher()' => [Reference::to(SymfonyEventDispatcherInterface::class)]
            ],
            CommandLoaderInterface::class => CommandLoader::class,
            SymfonyEventDispatcherInterface::class => EventDispatcher::class,
            InputInterface::class => static fn (): InputInterface => new ArgvInput(null, null),
            OutputInterface::class => ConsoleOutput::class,
            // View
            AssetLoaderInterface::class => AssetLoader::class,
            AssetPublisherInterface::class => AssetPublisher::class,
            AssetManager::class => [
                'class' => AssetManager::class,
                '__construct()' => [
                    Reference::to(Aliases::class),
                    Reference::to(AssetLoaderInterface::class)
                ],
                'withPublisher()' => [
                    Reference::to(AssetPublisherInterface::class)
                ]
            ],
            // Session
            SessionInterface::class => [
                'class' => Session::class,
                '__construct()' => [
                    ['cookie_secure' => 0]
                ]
            ],
            // Logger
            LoggerInterface::class => [
                'class' => Logger::class,
                '__construct()' => [[Reference::to(Target::class)]]
            ],
            // Cache
            CacheItemPoolInterface::class => fn (Aliases $aliases): CacheItemPoolInterface => new FilesystemAdapter('item-caches', 0, $aliases->get($this->config->runtimeDir)),
            CacheInterface::class => fn (Aliases $aliases): CacheInterface => new FileCache($aliases->get($this->config->runtimeDir . '/simple-caches')),
            YiiCacheInterface::class => Cache::class,
            // Profiler
            ProfilerInterface::class => Profiler::class,
            // Event
            ListenerCollection::class => fn (ListenerCollectionFactory $listenerCollectionFactory): ListenerCollection => $listenerCollectionFactory->create($this->config->events),
            ListenerProviderInterface::class => Provider::class,
            EventDispatcherInterface::class => Dispatcher::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
