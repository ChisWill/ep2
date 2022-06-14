<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Contract\InjectorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;

final class RequestHandlerFactory
{
    public function __construct(
        private ContainerInterface $container,
        private InjectorInterface $injector
    ) {
    }

    public function wrap(array $middlewares, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        krsort($middlewares);
        foreach ($this->buildMiddlewares($middlewares) as $middleware) {
            $handler = $this->wrapMiddleware($middleware, $handler);
        }
        return $handler;
    }

    public function create(Closure $callback): RequestHandlerInterface
    {
        return new class($this->injector, $callback) implements RequestHandlerInterface
        {
            public function __construct(
                private InjectorInterface $injector,
                private Closure $callback
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->injector->invoke($this->callback, [$request]);
            }
        };
    }

    private function buildMiddlewares(array $middlewares): iterable
    {
        foreach ($middlewares as $definition) {
            if (is_string($definition)) {
                yield $this->container->get($definition);
            } elseif ($definition instanceof MiddlewareInterface) {
                yield $definition;
            } elseif (is_callable($definition)) {
                yield $this->wrapCallback($definition);
            }
        }
    }

    private function wrapCallback(callable $callback): MiddlewareInterface
    {
        return new class($this->injector, $callback) implements MiddlewareInterface
        {
            private $callback;

            public function __construct(
                private InjectorInterface $injector,
                callable $callback
            ) {
                $this->callback = $callback;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->injector->invoke($this->callback, [$request, $handler]);
            }
        };
    }

    private function wrapMiddleware(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new class($middleware, $handler) implements RequestHandlerInterface
        {
            public function __construct(
                private MiddlewareInterface $middleware,
                private RequestHandlerInterface $handler
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->handler);
            }
        };
    }
}
