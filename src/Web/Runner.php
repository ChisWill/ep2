<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Config;
use Ep\Kit\ControllerParser;
use Ep\Kit\ControllerRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Closure;

final class runner
{
    public function __construct(
        private Config $config,
        private ControllerParser $parser,
        private ControllerRunner $runner
    ) {
        $this->parser = $parser->withSuffix($config->controllerSuffix);
    }

    public function run(string|array|Closure $handler, ServerRequestInterface $request): ResponseInterface
    {
        if ($handler instanceof Closure) {
            return $this->runner->runClosure($handler, $request);
        } else {
            [$controller, $action] = $this->parser->parse($handler);
            return $this->runner->runAction($controller, $action, $request);
        }
    }

    // /**
    //  * @param  ServerRequestInterface $request
    //  * 
    //  * @return ResponseInterface
    //  */
    // public function runAction(object $controller, string $action, $request, $response = null)
    // {
    //     // if ($middlewares = $controller->getMiddlewares()) {
    //     //     return $this->requestHandlerFactory
    //     //         ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapController($controller, $action)))
    //     //         ->handle($request);
    //     // } else {
    //     // }
    //     return parent::runAction($controller, $action, $request);
    // }

    // private function wrapController(Controller $controller, string $action): Closure
    // {
    //     return fn (ServerRequestInterface $request) => parent::runAction($controller, $action, $request);
    // }
}
