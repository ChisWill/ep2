<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\Contract\HandlerInterface;
use Ep\Base\Contract\InjectorInterface;
use Ep\Kit\Annotate;
use Yiisoft\Injector\Injector as YiiInjector;
use Psr\Container\ContainerInterface;

final class Injector implements InjectorInterface
{
    private YiiInjector $injector;

    public function __construct(
        private Annotate $annotate,
        ContainerInterface $container
    ) {
        $this->injector = new YiiInjector($container);
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $instance, string $method, array $arguments = []): mixed
    {
        if (method_exists($instance, Constant::METHOD_AROUND)) {
            array_unshift($arguments, $this->wrapCallHandler($instance, $method, $arguments));
            return $this->injector->invoke([$instance, Constant::METHOD_AROUND], $arguments);
        } else {
            return $this->annotate->method($instance, $method, $arguments);
        }
    }

    private function wrapCallHandler(object $instance, string $method, array $arguments): HandlerInterface
    {
        return new class($this->annotate, $instance, $method, $arguments) implements HandlerInterface
        {
            public function __construct(
                private Annotate $annotate,
                private object $instance,
                private string $method,
                private array $arguments
            ) {
            }

            public function handle(): mixed
            {
                return $this->annotate->method($this->instance, $this->method, $this->arguments);
            }
        };
    }

    /**
     * {@inheritDoc}
     */
    public function invoke(callable $callable, array $arguments = []): mixed
    {
        return $this->injector->invoke($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function make(string $class, array $arguments = []): mixed
    {
        $instance = $this->injector->make($class, $arguments);

        $this->annotate->property($instance, $arguments);

        return $instance;
    }
}
