<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Attribute\Middleware;
use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Base\Contract\InjectorInterface;
use Ep\Base\RouteParser;
use Ep\Kit\Annotate;
use Ep\Kit\Util;
use Ep\Web\Event\AfterRequest;
use Ep\Web\Event\BeforeRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Attribute;
use Closure;

final class runner
{
    public function __construct(
        private Config $config,
        private InjectorInterface $injector,
        private RouteParser $parser,
        private RequestHandlerFactory $requestHandlerFactory,
        private EventDispatcherInterface $eventDispatcher,
        private Annotate $annotate,
        private Util $util
    ) {
        $this->parser = $parser->withSuffix($config->controllerSuffix);
    }

    public function run(string|array|Closure $handler, ServerRequestInterface $request): ResponseInterface
    {
        if ($handler instanceof Closure) {
            return $this->runClosure($handler, $request);
        } else {
            [$controller, $action] = $this->parser->parse($handler);
            return $this->runMiddleware(
                $controller,
                $action,
                $request
                    ->withAttribute(
                        Constant::REQUEST_CONTROLLER,
                        $this->util->generateClassId(get_class($controller), $this->config->controllerSuffix)
                    )
                    ->withAttribute(Constant::REQUEST_ACTION, $action)
            );
        }
    }

    private function runMiddleware(object $controller, string $action, ServerRequestInterface $request): ResponseInterface
    {
        if ($middlewares = $this->annotate->getCache(Middleware::class, get_class($controller), Attribute::TARGET_CLASS)) {
            return $this->requestHandlerFactory
                ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapController($controller, $action)))
                ->handle($request);
        } else {
            return $this->runAction($controller, $action, $request);
        }
    }

    private function runAction(object $controller, string $action, ServerRequestInterface $request): ResponseInterface
    {
        $this->eventDispatcher->dispatch(new BeforeRequest($request));

        try {
            return $response = $this->injector->call($controller, $action, [$request]);
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $response ?? null));
        }
    }

    private function runClosure(Closure $callback, ServerRequestInterface $request): ResponseInterface
    {
        $this->eventDispatcher->dispatch(new BeforeRequest($request));

        try {
            return $response = $this->injector->invoke($callback, [$request]);
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $response ?? null));
        }
    }

    private function wrapController(object $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => $this->runAction($controller, $action, $request);
    }
}
