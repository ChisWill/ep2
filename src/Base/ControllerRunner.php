<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\Inject;
use Ep\Base\ControllerLoaderResult;
use Ep\Contract\ControllerInterface;
use Ep\Contract\InjectorInterface;
use Ep\Contract\ModuleInterface;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class ControllerRunner
{
    #[Inject]
    private ContainerInterface $container;
    #[Inject]
    private InjectorInterface $injector;
    #[Inject]
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function run(mixed $handler, mixed $request, mixed $response = null): mixed
    {
        return $this->runResult(
            $this->container
                ->get(ControllerLoader::class)
                ->withSuffix($this->getControllerSuffix())
                ->parse($handler),
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
    public function runResult(ControllerLoaderResult $result, $request, $response = null)
    {
        return $this->runAll($result->getModule(), $result->getController(), $result->getAction(), $request, $response);
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     * @throws NotFoundException
     */
    public function runAll(?ModuleInterface $module, ControllerInterface $controller, string $action, $request, $response = null)
    {
        $request = $this->eventDispatcher->dispatch(new BeforeRequest($request, $response))->getRequest();

        try {
            if ($module instanceof ModuleInterface) {
                return $result = $this->runModule($module, $controller, $action, $request, $response);
            } else {
                return $result = $this->runAction($controller, $action, $request, $response);
            }
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
        }
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     * @throws NotFoundException
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $controller, string $action, $request, $response = null)
    {
        $result = $module->before($request, $response);
        if ($result === true) {
            return $module->after($request, $this->runAction($controller, $action, $request, $response));
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
    protected function runAction(ControllerInterface $controller, string $action, $request, $response = null)
    {
        if (!is_callable([$controller, $action])) {
            throw new NotFoundException(sprintf('%s::%s() is not found.', get_class($controller), $action));
        }

        $result = $controller->before($request, $response);
        if ($result === true) {
            return $controller->after($request, $this->injector->call($controller, $action, array_filter([$request, $response])));
        } else {
            return $result;
        }
    }

    abstract public function getControllerSuffix(): string;
}
