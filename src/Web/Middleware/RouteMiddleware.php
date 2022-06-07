<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Base\Router;
use Ep\Exception\NotFoundException;
use Ep\Web\ControllerRunner;
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
        private ControllerRunner $controllerRunner,
        private ResponseFactoryInterface $responseFactory,
        Config $config
    ) {
        $this->router = $router
            ->withEnableDefaultRule($config->enableDefaultRouteRule)
            ->withDefaultRule($config->defaultRouteRule);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            [$allowed, $result, $params] = $this->router->match(
                $request->getUri()->getPath(),
                $request->getMethod()
            );

            if (!$allowed) {
                return $this->responseFactory->createResponse(Status::METHOD_NOT_ALLOWED);
            }

            foreach ($params as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            return $this->controllerRunner->run($result, $request);
        } catch (NotFoundException $e) {
            return $handler->handle(
                $request->withAttribute(Constant::REQUEST_ATTRIBUTE_EXCEPTION, $e)
            );
        }
    }
}
