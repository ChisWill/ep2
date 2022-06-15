<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

use Throwable;

interface ErrorRendererInterface
{
    public function render(Throwable $t, mixed $request): string;
}
