<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\EnvInterface;
use Ep\Contract\InjectorInterface;
use Ep\Kit\Annotate;
use Ep\Kit\Util;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\ContainerConfigInterface;
use Yiisoft\Factory\Factory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class Core
{
    private EnvInterface $env;
    private Config $config;

    public function create(string $rootPath, string $configPath): self
    {
        $this->env = new Env($rootPath, $configPath);
        $this->config = $this->env->getConfig();

        return $this;
    }

    private ContainerConfig $containerConfig;
    private ContainerInterface $container;

    public function ready(string $application): object
    {
        $this->containerConfig = $this->createContainerConfig($application::getDiProviderName());
        $this->container = (new Container($this->containerConfig))->get(ContainerInterface::class);
        return $this->container->get($application);
    }

    public function getContainerConfig(): ContainerConfig
    {
        return $this->containerConfig;
    }

    public function getEnv(): EnvInterface
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

    public function getDb(string $id = null): Connection
    {
        return $this->container->get($id ?? Connection::class);
    }

    public function getCache(string $id = null): CacheInterface
    {
        return $this->container->get($id ?? CacheInterface::class);
    }

    public function getLogger(string $id = null): LoggerInterface
    {
        return $this->container->get($id ?? LoggerInterface::class);
    }

    public function scan(): void
    {
        $this->container
            ->get(Annotate::class)
            ->cache(
                $this->container
                    ->get(Util::class)
                    ->getClassList($this->config->rootNamespace)
            );
    }

    public function isSelf(string $rootNamespace = null): bool
    {
        return ($rootNamespace ?? $this->config->rootNamespace) === 'Ep';
    }

    private function createContainerConfig(string $appProvider): ContainerConfigInterface
    {
        $providers = [
            new ServiceProvider($this->env),
            new $appProvider($this->config),
        ];
        if ($this->config->diProvider) {
            $providers[] = new $this->config->diProvider($this->config);
        }
        return ContainerConfig::create()
            ->withProviders($providers)
            ->withValidate($this->config->debug);
    }
}
