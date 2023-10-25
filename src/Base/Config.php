<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Console\Application as ConsoleApplication;
use Ep\Console\DiProvider as ConsoleDiProvider;
use Ep\Web\Application as WebApplication;
use Ep\Web\DiProvider as WebProvider;
use InvalidArgumentException;

/**
 * @property string $rootNamespace the application root namespace
 * @property array $aliases the path aliases
 * @property bool $debug whether start applicaion in debug mode
 * @property string $env the current environment
 * @property string $controllerSuffix the web controller suffix
 * @property string $commandSuffix the console command suffix
 * @property string $defaultController the default Controller name
 * @property string $defaultAction the default action name
 * @property bool $enableDefaultRouteRule whether enable the default route rule
 * @property ?array $defaultRouteRule the default route rule
 * @property string $routeDir the route directory
 * @property string $runtimeDir the runtime directory
 * @property string $vendorPath the vendor directory
 * @property string $viewPath the view directory
 * @property string $layoutDir the layout directory
 * @property string $cipherMethod the cipher method
 * @property ?string $secretKey the application secretKey what should be generate by command "./ep generate/key"
 * @property ?string $diProviderClass the classname of instance what implements interface "Yiisoft\Di\ServiceProviderInterface"
 * @property array $diProviderMap the default di provider mapping 
 * @property array $params the user parameters
 */
final class Config
{
    /**
     * The application root namespace
     */
    private string $rootNamespace = 'App';
    /**
     * The path aliases
     */
    private array $aliases = [];
    /**
     * Whether start applicaion in debug mode
     */
    private bool $debug = true;
    /**
     * The current environment
     */
    private string $env = 'prod';
    /**
     * The web controller suffix
     */
    private string $controllerSuffix = 'Controller';
    /**
     * The console command suffix
     */
    private string $commandSuffix = 'Command';
    /**
     * The default Controller name
     */
    private string $defaultController = 'index';
    /**
     * The default action name
     */
    private string $defaultAction = 'index';
    /**
     * Whether enable the default route rule
     */
    private bool $enableDefaultRouteRule = true;
    /**
     * The default route rule
     */
    private ?array $defaultRouteRule = null;
    /**
     * The route directory
     */
    private string $routeDir = '@root/routes';
    /**
     * The runtime directory
     */
    private string $runtimeDir = '@root/runtime';
    /**
     * The vendor directory
     */
    private string $vendorPath = '@root/vendor';
    /**
     * The view directory
     */
    private string $viewPath = '@root/views';
    /**
     * The layout directory
     */
    private string $layoutDir = '_layouts';
    /**
     * The cipher method
     */
    private string $cipherMethod = 'AES-128-CBC';
    /**
     * The application secretKey what should be generate by command "./ep generate/key"
     */
    private ?string $secretKey = null;
    /**
     * The classname of instance what implements interface "Yiisoft\Di\ServiceProviderInterface"
     */
    private ?string $diProviderClass = null;
    /**
     * The default di provider mapping
     */
    private array $diProviderMap = [
        WebApplication::class => WebProvider::class,
        ConsoleApplication::class => ConsoleDiProvider::class
    ];
    /**
     * The user parameters
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
