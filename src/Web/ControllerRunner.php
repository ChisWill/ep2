<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Config;
use Ep\Base\ControllerRunner as BaseControllerRunner;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Closure;

final class ControllerRunner extends BaseControllerRunner
{
    public function __construct(
        private ContainerInterface $container,
        private Config $config,
        private RequestHandlerFactory $requestHandlerFactory
    ) {
    }

    /**
     * {@inheritDoc}
     * 
     * @param  ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    protected function runAction(object $controller, string $action, $request, $response = null)
    {
        // if ($middlewares = $controller->getMiddlewares()) {
        //     return $this->requestHandlerFactory
        //         ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapController($controller, $action)))
        //         ->handle($request);
        // } else {
        // }
        return parent::runAction($controller, $action, $request);
    }

    private function wrapController(Controller $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => parent::runAction($controller, $action, $request);
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerSuffix(): string
    {
        return $this->config->controllerSuffix;
    }
}
