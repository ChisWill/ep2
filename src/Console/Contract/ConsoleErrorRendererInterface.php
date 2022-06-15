<?php

declare(strict_types=1);

namespace Ep\Console\Contract;

use Throwable;

interface ConsoleErrorRendererInterface
{
    public function render(Throwable $t, ConsoleRequestInterface $request): string;
}
