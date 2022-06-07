<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ControllerInterface;

final class ControllerLoaderResult
{
    public function __construct(
        private ControllerInterface $controller,
        private string $action
    ) {
    }

    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
