<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Contract\InjectorInterface;
use Ep\Base\Event\AfterRequest;
use Ep\Base\Event\BeforeRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Closure;

final class ControllerRunner
{
    public function __construct(
        private InjectorInterface $injector,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function runAction(object $controller, string $action, mixed $request, mixed $response = null): mixed
    {
        $this->eventDispatcher->dispatch(new BeforeRequest($request, $response));

        try {
            return $this->injector->call($controller, $action, array_filter([$request, $response]));
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }

    public function runClosure(Closure $callback, mixed $request, mixed $response = null): mixed
    {
        $this->eventDispatcher->dispatch(new BeforeRequest($request, $response));

        try {
            return $this->injector->invoke($callback, array_filter([$request, $response]));
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }
}
