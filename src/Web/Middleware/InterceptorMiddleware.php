<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Web\Contract\InterceptorInterface;
use Ep\Web\RequestHandlerFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InterceptorMiddleware implements MiddlewareInterface
{
    private array $includePath = [];
    private array $excludePath = [];

    public function __construct(
        private RequestHandlerFactory $requestHandlerFactory,
        InterceptorInterface $interceptor = null
    ) {
        if ($interceptor === null) {
            return;
        }

        foreach ($interceptor->includePath() as $path => $class) {
            $this->includePath['/' . trim($path, '/')] = (array) $class;
        }
        foreach ($interceptor->excludePath() as $path => $class) {
            $this->excludePath['/' . trim($path, '/')] = (array) $class;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getUri()->getPath();
        $middlewares = [];
        foreach ($this->includePath as $path => $class) {
            if (str_starts_with($requestPath, $path)) {
                $middlewares = array_merge($middlewares, $class);
            }
        }
        foreach ($this->excludePath as $path => $class) {
            if (!str_starts_with($requestPath, $path)) {
                $middlewares = array_merge($middlewares, $class);
            }
        }

        if ($middlewares) {
            return $this->requestHandlerFactory
                ->wrap($middlewares, $handler)
                ->handle($request);
        } else {
            return $handler->handle($request);
        }
    }
}
