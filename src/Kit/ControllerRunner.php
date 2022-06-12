<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Contract\InjectorInterface;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Exception\PageNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Closure;

final class ControllerRunner
{
    public function __construct(
        private InjectorInterface $injector,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws PageNotFoundException
     */
    public function runAction(object $controller, string $action, mixed $request, mixed $response = null): mixed
    {
        if (!is_callable([$controller, $action])) {
            throw new PageNotFoundException(sprintf('%s::%s() is not exists.', get_class($controller), $action));
        }

        $request = $this->eventDispatcher->dispatch(new BeforeRequest($request, $response))->getRequest();

        try {
            return $this->injector->call($controller, $action, array_filter([$request, $response]));
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }

    public function runClosure(Closure $callback, mixed $request, mixed $response = null): mixed
    {
        $request = $this->eventDispatcher->dispatch(new BeforeRequest($request, $response))->getRequest();

        try {
            return $this->injector->invoke($callback, array_filter([$request, $response]));
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }
}
