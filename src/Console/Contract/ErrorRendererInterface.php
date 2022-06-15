<?php

declare(strict_types=1);

namespace Ep\Console\Contract;

use Throwable;

interface ErrorRendererInterface
{
    public function render(Throwable $t, RequestInterface $request): string;
}
