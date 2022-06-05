<?php

declare(strict_types=1);

namespace Ep\Base;

use Closure;
use InvalidArgumentException;

/**
 * The properties of Config are readonly
 */
final class Config
{
    /**
     * Application root namespace
     */
    public string $rootNamespace = 'App';
    /**
     * Path aliases
     */
    public array $aliases = [];
    /**
     * Whether start applicaion in debug mode
     */
    public bool $debug = true;
    /**
     * Current environment
     */
    public string $env = 'prod';
    /**
     * Module class
     */
    public string $moduleName = 'Module';
    /**
     * Web controller suffix
     */
    public string $controllerSuffix = 'Controller';
    /**
     * Console command suffix
     */
    public string $commandSuffix = 'Command';
    /**
     * Database migration table name
     */
    public string $migrationTableName = 'migration';
    /**
     * Action suffix
     */
    public string $actionSuffix = 'Action';
    /**
     * Default Controller
     */
    public string $defaultController = 'index';
    /**
     * Default action
     */
    public string $defaultAction = 'index';
    /**
     * Whether enable the default route rule
     */
    public bool $enableDefaultRouteRule = true;
    /**
     * Default route rule
     */
    public ?array $defaultRouteRule = null;
    /**
     * Runtime directory
     */
    public string $runtimeDir = '@root/runtime';
    /**
     * Vendor directory
     */
    public string $vendorPath = '@root/vendor';
    /**
     * View directory
     */
    public string $viewPath = '@root/views';
    /**
     * Layout directory
     */
    public string $layoutDir = '_layouts';
    /**
     * Events
     */
    public array $events = [];
    /**
     * Application secretKey
     */
    public ?string $secretKey = null;
    /**
     * The custom parameters
     */
    public array $params = [];
    /**
     * The custom DI configuration
     * 
     * ```php
     * 
     * return static fn (\Ep\Base\Config $config, array $params): array => [
     *     FooInterface::class => Foo::class
     * ];
     * 
     * ```
     */
    private ?Closure $di = null;

    public function __construct(array $config)
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }

    public function __set(string $name, mixed $value): void
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }

    /**
     * Call before the application runs
     */
    public function switch(array $properties): array
    {
        $result = [];
        foreach ($properties as $key => $value) {
            $result[$key] = $this->$key;
            $this->$key = $value;
        }
        return $result;
    }

    public function getDi(): array
    {
        return $this->di ? call_user_func($this->di, $this, $this->params) : [];
    }
}
