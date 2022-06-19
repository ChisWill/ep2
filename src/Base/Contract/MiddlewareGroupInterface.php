<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

interface MiddlewareGroupInterface
{
    public function getMiddlewares(): array;
}
