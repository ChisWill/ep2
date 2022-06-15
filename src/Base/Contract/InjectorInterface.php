<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

interface InjectorInterface
{
    public function call(object $instance, string $method, array $arguments = []): mixed;

    public function invoke(callable $callable, array $arguments = []): mixed;

    public function make(string $class, array $arguments = []): mixed;
}
