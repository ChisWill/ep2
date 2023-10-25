<?php

declare(strict_types=1);

namespace Ep\Web\Contract;

interface MiddlewareGroupInterface
{
    public function getMiddlewares(): array;
}
