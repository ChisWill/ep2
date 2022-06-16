<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Contract\InjectorInterface;
use Ep\Console\Contract\RequestInterface;
use Ep\Console\Contract\ResponseInterface;

final class Runner
{
    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    public function runAction(object $controller, string $action, RequestInterface $request, ResponseInterface $response = null): ResponseInterface
    {
        return $this->injector->call($controller, $action, array_filter([$request, $response]));
    }
}
