<?php

declare(strict_types=1);

namespace Ep\Base;

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
     * Web controller suffix
     */
    public string $controllerSuffix = 'Controller';
    /**
     * Console command suffix
     */
    public string $commandSuffix = 'Command';
    /**
     * Default Controller
     */
    public string $defaultController = 'index';
    /**
     * Default action
     */
    public string $defaultAction = 'index';
    /**
     * Database migration table name
     */
    public string $migrationTableName = 'migration';
    /**
     * Whether enable the default route rule
     */
    public bool $enableDefaultRouteRule = true;
    /**
     * Default route rule
     */
    public ?array $defaultRouteRule = null;
    /**
     * Route directory
     */
    public string $routeDir = '@root/routes';
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
     * The application secretKey what should be generate by command "./ep generate/key"
     */
    public ?string $secretKey = null;
    /**
     * The classname of instance what implements interface "Yiisoft\Di\ServiceProviderInterface"
     */
    public ?string $diProvider = null;
    /**
     * User parameters
     */
    public array $params = [];

    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __set(string $name, mixed $value): void
    {
        throw new InvalidArgumentException("The configuration \"{$name}\" can't be set.");
    }
}
