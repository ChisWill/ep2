<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\AfterAction;
use Ep\Attribute\BeforeAction;
use Ep\Attribute\Inject;
use Ep\Contract\InjectorInterface;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Exception\NotFoundException;
use Ep\Kit\Annotate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Attribute;
use Closure;

abstract class ControllerRunner
{
    #[Inject]
    private ContainerInterface $container;
    #[Inject]
    private InjectorInterface $injector;
    #[Inject]
    private EventDispatcherInterface $eventDispatcher;
    #[Inject]
    private Annotate $annotate;

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function run(string|array|Closure $handler, mixed $request, mixed $response = null): mixed
    {
        if ($handler instanceof Closure) {
            return $this->runClosure($handler, $request, $response);
        } else {
            [$controller, $action] = $this->container
                ->get(ControllerLoader::class)
                ->withSuffix($this->getControllerSuffix())
                ->parse($handler);
            return $this->runAction($controller, $action, $request, $response);
        }
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function runClosure(Closure $handler, $request, $response = null)
    {
        return $this->runInternal(
            fn (mixed $request, mixed $response): mixed => $this->injector->invoke($handler, array_filter([$request, $response])),
            $request,
            $response
        );
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     * @throws NotFoundException
     */
    protected function runAction(object $controller, string $action, $request, $response = null)
    {
        if (!is_callable([$controller, $action])) {
            throw new NotFoundException(sprintf('%s::%s() is not found.', get_class($controller), $action));
        }

        if ($before = current($this->annotate->getCache(BeforeAction::class, get_class($controller), Attribute::TARGET_METHOD))) {
            $result = call_user_func([$controller, $before['target']], $request, $response);
        } else {
            $result = true;
        }

        if ($result === true) {
            $result = $this->injector->call($controller, $action, array_filter([$request, $response]));
            if ($after = current($this->annotate->getCache(AfterAction::class, get_class($controller), Attribute::TARGET_METHOD))) {
                return call_user_func([$controller, $after['target']], $request, $result);
            } else {
                return $result;
            }
        } else {
            return $result;
        }
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     * @throws NotFoundException
     */
    private function runInternal(callable $callback, $request, $response = null)
    {
        $request = $this->eventDispatcher->dispatch(new BeforeRequest($request, $response))->getRequest();

        try {
            return call_user_func($callback);
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }

    abstract public function getControllerSuffix(): string;
}
