<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Base\Router;
use Ep\Exception\PageNotFoundException;
use Ep\Web\Runner;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Router $router,
        private Runner $runner,
        private ResponseFactoryInterface $responseFactory,
        Config $config
    ) {
        $this->router = $router
            ->withSuffix($config->controllerSuffix)
            ->withEnableDefaultRule($config->enableDefaultRouteRule)
            ->withDefaultRule($config->defaultRouteRule);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $fallback): ResponseInterface
    {
        try {
            [$allowed, $handler, $params] = $this->router->match(
                $request->getUri()->getPath(),
                $request->getMethod()
            );

            if (!$allowed) {
                return $this->responseFactory->createResponse(Status::METHOD_NOT_ALLOWED);
            }

            foreach ($params as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            return $this->runner->run($handler, $request);
        } catch (PageNotFoundException $e) {
            return $fallback->handle(
                $request->withAttribute(Constant::REQUEST_ATTRIBUTE_EXCEPTION, $e)
            );
        }
    }
}
