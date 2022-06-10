<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ApplicationInterface;
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
use LogicException;

final class Core
{
    private string $rootPath;
    private EnvInterface $env;

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
    private ContainerConfig $containerConfig;
    private ContainerInterface $container;

    public function ready(string $application): object
    {
        if (!is_callable([$application, 'getDiProviderName'])) {
            throw new LogicException(sprintf('The application "%s" must implements %s', $application, ApplicationInterface::class));
        }
        $this->config ??= $this->createConfig();
        $this->containerConfig ??= $this->createContainerConfig($application::getDiProviderName());
        $this->container ??= (new Container($this->containerConfig))->get(ContainerInterface::class);
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

    private function createConfig(): Config
    {
        if (is_string($this->configValue)) {
            $value = require($this->rootPath . '/' . ltrim($this->configValue, './'));
        } elseif (is_array($this->configValue)) {
            $value = $this->configValue;
        } else {
            $value = [];
        }
        return new Config($value);
    }

    private function createContainerConfig(?string $appDiProvider): ContainerConfigInterface
    {
        $providers = [
            new DiProvider($this->env, $this->config)
        ];
        if ($appDiProvider) {
            $providers[] = new $appDiProvider($this->config);
        }
        if ($this->config->diProvider) {
            $providers[] = new $this->config->diProvider($this->config);
        }
        return ContainerConfig::create()
            ->withProviders($providers)
            ->withValidate($this->config->debug);
    }
}
