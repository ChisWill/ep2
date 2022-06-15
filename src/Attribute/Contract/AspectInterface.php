<?php

declare(strict_types=1);

namespace Ep\Attribute\Contract;

use Ep\Base\Contract\HandlerInterface;

interface AspectInterface
{
    public function handle(HandlerInterface $handler): mixed;
}
