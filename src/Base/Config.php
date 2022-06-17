<?php

declare(strict_types=1);

namespace Ep\Base;

use InvalidArgumentException;

/**
 * @property string $rootNamespace Application root namespace
 * @property array $aliases Path aliases
 * @property bool $debug Whether start applicaion in debug mode
 * @property string $env Current environment
 * @property string $controllerSuffix Web controller suffix
 * @property string $commandSuffix Console command suffix
 * @property string $defaultController Default Controller name
 * @property string $defaultAction Default action name
 * @property bool $enableDefaultRouteRule Whether enable the default route rule
 * @property ?array $defaultRouteRule Default route rule
 * @property string $routeDir Route directory
 * @property string $runtimeDir Runtime directory
 * @property string $vendorPath Vendor directory
 * @property string $viewPath View directory
 * @property string $layoutDir Layout directory
 * @property ?string $secretKey The application secretKey what should be generate by command "./ep generate/key"
 * @property ?string $diProvider The classname of instance what implements interface "Yiisoft\Di\ServiceProviderInterface"
 * @property array $params User parameters
 */
final class Config
{
    /**
     * Application root namespace
     */
    private string $rootNamespace = 'App';
    /**
     * Path aliases
     */
    private array $aliases = [];
    /**
     * Whether start applicaion in debug mode
     */
    private bool $debug = true;
    /**
     * Current environment
     */
    private string $env = 'prod';
    /**
     * Web controller suffix
     */
    private string $controllerSuffix = 'Controller';
    /**
     * Console command suffix
     */
    private string $commandSuffix = 'Command';
    /**
     * Default Controller name
     */
    private string $defaultController = 'index';
    /**
     * Default action name
     */
    private string $defaultAction = 'index';
    /**
     * Whether enable the default route rule
     */
    private bool $enableDefaultRouteRule = true;
    /**
     * Default route rule
     */
    private ?array $defaultRouteRule = null;
    /**
     * Route directory
     */
    private string $routeDir = '@root/routes';
    /**
     * Runtime directory
     */
    private string $runtimeDir = '@root/runtime';
    /**
     * Vendor directory
     */
    private string $vendorPath = '@root/vendor';
    /**
     * View directory
     */
    private string $viewPath = '@root/views';
    /**
     * Layout directory
     */
    private string $layoutDir = '_layouts';
    /**
     * The application secretKey what should be generate by command "./ep generate/key"
     */
    private ?string $secretKey = null;
    /**
     * The classname of instance what implements interface "Yiisoft\Di\ServiceProviderInterface"
     */
    private ?string $diProvider = null;
    /**
     * User parameters
     */
    private array $params = [];

    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        throw new InvalidArgumentException("The configuration \"{$name}\" can't be set.");
    }
}
