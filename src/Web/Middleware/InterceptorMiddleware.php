<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Contract\FilterInterface;
use Ep\Contract\InterceptorInterface;
use Ep\Web\RequestHandlerFactory;
use Yiisoft\Http\Status;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InterceptorMiddleware implements MiddlewareInterface
{
    private array $includePath = [];
    private array $excludePath = [];

    public function __construct(
        private ContainerInterface $container,
        private RequestHandlerFactory $requestHandlerFactory,
        private ResponseFactoryInterface $responseFactory,
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
        $classList = [];
        foreach ($this->includePath as $path => $class) {
            if (str_starts_with($requestPath, $path)) {
                $classList = array_merge($classList, $class);
            }
        }
        foreach ($this->excludePath as $path => $class) {
            if (!str_starts_with($requestPath, $path)) {
                $classList = array_merge($classList, $class);
            }
        }

        $stack = [];
        $middlewares = [];
        foreach ($classList as $class) {
            /** @var FilterInterface */
            $filter = $this->container->get($class);
            $result = $filter->before($request);
            if ($result === true) {
                $stack[] = $filter;
                $middlewares = array_merge($middlewares, $filter->getMiddlewares());
            } elseif ($result instanceof ResponseInterface) {
                return $result;
            } else {
                return $this->responseFactory->createResponse(Status::NOT_ACCEPTABLE);
            }
        }

        if ($middlewares) {
            $response = $this->requestHandlerFactory
                ->wrap($middlewares, $handler)
                ->handle($request);
        } else {
            $response = $handler->handle($request);
        }

        while ($filter = array_pop($stack)) {
            /** @var FilterInterface $filter */
            $response = $filter->after($request, $response);
        }

        return $response;
    }
}
