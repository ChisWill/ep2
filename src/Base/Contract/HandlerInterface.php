<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

interface HandlerInterface
{
    public function handle(): mixed;
}
