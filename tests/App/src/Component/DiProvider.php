<?php

declare(strict_types=1);

namespace Ep\Base;

namespace Ep\Tests\App\Component;

use Ep\Base\Config;
use Ep\Auth\AuthRepository;
use Ep\Auth\Method\HttpSession;
use Ep\Base\Contract\EventListenerInterface;
use Ep\Console\Contract\ErrorRendererInterface as ConsoleErrorRendererInterface;
use Ep\Base\Contract\InjectorInterface;
use Ep\Web\Contract\InterceptorInterface;
use Ep\Web\Contract\ErrorRendererInterface as WebErrorRendererInterface;
use Ep\Tests\App\Component\AuthFailHandler;
use Ep\Tests\App\Component\ErrorRenderer;
use Ep\Tests\App\Component\Interceptor;
use Ep\Tests\App\Component\UserRepository;
use Ep\Tests\Support\Car\Car;
use Ep\Tests\Support\Car\CarInterface;
use Ep\Tests\Support\Car\Wheel;
use Ep\Tests\Support\Car\WheelInterface;
use Ep\Tests\Support\Object\Engine\EngineInterface;
use Ep\Tests\Support\Object\Engine\SteamEngine;
use Ep\Tests\Support\Object\Wing\AngelWing;
use Ep\Tests\Support\Object\Wing\WingInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Mysql\ConnectionPDO as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Db\Sqlite\ConnectionPDO as SqliteConnection;
use Yiisoft\Db\Sqlite\PDODriver;
use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target;
use Yiisoft\Log\Target\File\FileTarget;

final class DiProvider implements ServiceProviderInterface
{
    private array $params;

    public function __construct(private Config $config)
    {
        $this->params = $config->params;
    }

    public function getDefinitions(): array
    {
        return [
            EventListenerInterface::class => EventListener::class,
            WebErrorRendererInterface::class => ErrorRenderer::class,
            ConsoleErrorRendererInterface::class => ConsoleRenderer::class,
            InterceptorInterface::class => Interceptor::class,
            AuthRepository::class => static function (InjectorInterface $injector): AuthRepository {
                return $injector
                    ->make(AuthRepository::class)
                    ->setMethod('frontend', $injector->make(HttpSession::class, [
                        new UserRepository()
                    ]))
                    ->bindFailureHandler(HttpSession::class, AuthFailHandler::class);
            },
            // Log
            Target::class => fn (Aliases $aliases): FileTarget => new FileTarget($aliases->get($this->config->runtimeDir . '/logs/' . date('Y-m-d') . '.log')),
            'alert' => fn (Aliases $aliases): LoggerInterface => new Logger([new FileTarget($aliases->get($this->config->runtimeDir) . '/alerts/' . date('Ymd') . '.log')]),
            // Sqlite
            'sqlite' => [
                'class' => SqliteConnection::class,
                '__construct()' => [new PDODriver('sqlite:' . dirname(__FILE__, 3) . '/config/ep.sqlite')],
            ],
            // Redis
            RedisConnection::class => [
                'class' => RedisConnection::class,
                'hostname()' => [$this->params['db']['redis']['hostname']],
                'database()' => [$this->params['db']['redis']['database']],
                'password()' => [$this->params['db']['redis']['password']],
                'port()'     => [$this->params['db']['redis']['port']]
            ],
            // Mysql
            Connection::class => [
                'class' => MysqlConnection::class,
                '__construct()' => [$this->params['db']['mysql']['dsn']],
                'setUsername()' => [$this->params['db']['mysql']['username']],
                'setPassword()' => [$this->params['db']['mysql']['password']]
            ],

            // Others
            WingInterface::class => [
                'class' => AngelWing::class,
                'addSpeed()' => [
                    20
                ]
            ],
            EngineInterface::class => SteamEngine::class,
            WheelInterface::class => Wheel::class,
            CarInterface::class => Car::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
