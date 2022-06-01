<?php

declare(strict_types=1);

namespace Ep\Contract\Attribute;

use Ep\Contract\HandlerInterface;

interface AspectInterface
{
    public function handle(HandlerInterface $handler): mixed;
}
