<?php

declare(strict_types=1);

namespace Ep\Attribute;

use Ep\Contract\Attribute\ConfigureInterface;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Middleware implements ConfigureInterface
{
    public function __construct(
        private array $middlewares
    ) {
    }

    public function getValues(): array
    {
        return $this->middlewares;
    }
}
