<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Attribute\Middleware;
use Ep\Base\Config;
use Ep\Kit\Annotate;
use Ep\Kit\ControllerParser;
use Ep\Kit\ControllerRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Attribute;
use Closure;

final class runner
{
    public function __construct(
        private ControllerParser $parser,
        private ControllerRunner $runner,
        private RequestHandlerFactory $requestHandlerFactory,
        private Annotate $annotate,
        Config $config
    ) {
        $this->parser = $parser->withSuffix($config->controllerSuffix);
    }

    public function run(string|array|Closure $handler, ServerRequestInterface $request): ResponseInterface
    {
        if ($handler instanceof Closure) {
            return $this->runner->runClosure($handler, $request);
        } else {
            [$controller, $action] = $this->parser->parse($handler);
            return $this->runAction($controller, $action, $request);
        }
    }

    private function runAction(object $controller, string $action, ServerRequestInterface $request): ResponseInterface
    {
        if ($middlewares = $this->annotate->getCache(Middleware::class, get_class($controller), Attribute::TARGET_CLASS)) {
            return $this->requestHandlerFactory
                ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapController($controller, $action)))
                ->handle($request);
        } else {
            return $this->runner->runAction($controller, $action, $request);
        }
    }

    private function wrapController(object $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => $this->runner->runAction($controller, $action, $request);
    }
}
