<?php

declare(strict_types=1);

namespace Ep\Web\Trait;

use Ep\Web\MiddlewareDefinition;

trait Middleware
{
    private array $__middlewares = [];

    protected function middleware(array $middlewares): MiddlewareDefinition
    {
        $definition = new MiddlewareDefinition($middlewares);

        $this->__middlewares[] = $definition;

        return $definition;
    }

    public function __getMiddlewares(string $action): array
    {
        return array_filter($this->__middlewares, static fn (MiddlewareDefinition $definition): bool => $definition->filter($action));
    }
}
