<?php

declare(strict_types=1);

namespace Ep\Contract\Attribute;

use Reflector;

interface ProcessInterface
{
    public function process(object $instance, Reflector $reflector, array $arguments = []): void;
}
