<?php

declare(strict_types=1);

namespace Ep\Auth;

use Ep\Base\Contract\InjectorInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\Middleware\Authentication;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;

final class AuthRepository
{
    public function __construct(
        private ContainerInterface $container,
        private InjectorInterface $injector
    ) {
    }

    private array $methods = [];

    public function setMethod(string $method, AuthenticationMethodInterface $instance = null): self
    {
        $this->methods[$method] = $instance;
        return $this;
    }

    private array $failureHandlers = [];

    public function bindFailureHandler(string $method, string|RequestHandlerInterface $handler): self
    {
        $this->failureHandlers[$method] = $handler;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findMethod(string $method): AuthenticationMethodInterface
    {
        $this->validate($method);

        if (!isset($this->methods[$method])) {
            $this->methods[$method] = $this->container->get($method);
        }
        return $this->methods[$method];
    }

    private array $middlewares = [];

    /**
     * @throws InvalidArgumentException
     */
    public function findMiddleware(string $method): MiddlewareInterface
    {
        $this->validate($method);

        if (!isset($this->middlewares[$method])) {
            $arguments = [
                $this->findMethod($method)
            ];
            if (isset($this->failureHandlers[$method])) {
                if (is_string($this->failureHandlers[$method])) {
                    $this->failureHandlers[$method] = $this->container->get($this->failureHandlers[$method]);
                }
                $arguments[] = $this->failureHandlers[$method];
            }
            $this->middlewares[$method] = $this->injector->make(Authentication::class, $arguments);
        }

        return $this->middlewares[$method];
    }

    private function validate(string $method): void
    {
        if (!array_key_exists($method, $this->methods)) {
            throw new InvalidArgumentException('Invalid authentication method.');
        }
    }
}
