<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;

final class ControllerLoaderResult
{
    public function __construct(
        private ?ModuleInterface $module,
        private ControllerInterface $controller,
        private string $action
    ) {
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
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
