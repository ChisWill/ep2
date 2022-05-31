<?php

declare(strict_types=1);

namespace Ep\Contract;

interface HandlerInterface
{
    public function handle(): mixed;
}
