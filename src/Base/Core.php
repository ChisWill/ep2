<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\Contract\InjectorInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\ContainerConfigInterface;
use Yiisoft\Factory\Factory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use LogicException;

final class Core
{
    private string $rootPath;
    private Env $env;

    public function create(string $rootPath): self
    {
        $this->rootPath ??= $rootPath;
        $this->env ??= new Env($this->rootPath);
        return $this;
    }

    private string|array $configValue = 'config/main.php';

    public function config(string|array $value): self
    {
        $this->configValue = $value;
        return $this;
    }

    private Config $config;
    private ContainerInterface $container;

    /**
     * @throws LogicException
     */
    public function ready(string $application): object
    {
        $this->config ??= $this->createConfig();
        $this->container ??= new Container(
            $this->createContainerConfig($application, $this->env, $this->config)
        );
        return $this->container->get($application);
    }

    public function getEnv(): Env
    {
        return $this->env;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDi(): ContainerInterface
    {
        return $this->container;
    }

    public function getFactory(): Factory
    {
        return $this->container->get(Factory::class);
    }

    public function getInjector(): InjectorInterface
    {
        return $this->container->get(InjectorInterface::class);
    }

    public function getDb(string $id = null): ConnectionInterface
    {
        return $this->container->get($id ?? ConnectionInterface::class);
    }

    public function getCache(string $id = null): CacheInterface
    {
        return $this->container->get($id ?? CacheInterface::class);
    }

    public function getLogger(string $id = null): LoggerInterface
    {
        return $this->container->get($id ?? LoggerInterface::class);
    }

    private function createConfig(): Config
    {
        if (is_string($this->configValue)) {
            return new Config(require($this->rootPath . '/' . ltrim($this->configValue, './')));
        } else {
            return new Config($this->configValue);
        }
    }

    private function createContainerConfig(string $application, Env $env, Config $config): ContainerConfigInterface
    {
        $providers = [
            new DiProvider($env, $config)
        ];

        if (isset($config->diProviderMap[$application])) {
            $providers[] = new $config->diProviderMap[$application]();
        }

        if ($config->diProviderClass) {
            $providers[] = new $config->diProviderClass($config);
        }

        return ContainerConfig::create()
            ->withProviders($providers)
            ->withValidate($config->debug);
    }
}
