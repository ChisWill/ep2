<?php

declare(strict_types=1);

namespace Ep\Base;

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
        return $this->annotate->method($instance, $method, $arguments);
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
