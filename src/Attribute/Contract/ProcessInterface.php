<?php

declare(strict_types=1);

namespace Ep\Attribute\Contract;

use Reflector;

interface ProcessInterface
{
    public function process(object $instance, Reflector $reflector, array $arguments = []): void;
}
