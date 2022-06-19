<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Contract\MiddlewareGroupInterface;

final class MiddlewareDefinition implements MiddlewareGroupInterface
{
    public function __construct(private array $middlewares)
    {
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function filter(string $action): bool
    {
        return (!$this->only || in_array($action, $this->only))
            && (!$this->except || !in_array($action, $this->except));
    }

    private array $only = [];

    public function only(array $only): self
    {
        $this->only = $only;
        return $this;
    }

    private array $except = [];

    public function except(array $except): self
    {
        $this->except = $except;
        return $this;
    }
}
