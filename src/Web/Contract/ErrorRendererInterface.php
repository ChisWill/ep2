<?php

declare(strict_types=1);

namespace Ep\Web\Contract;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorRendererInterface
{
    public function render(Throwable $t, ServerRequestInterface $request): string;
}
