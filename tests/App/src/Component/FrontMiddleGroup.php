<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Web\Contract\MiddlewareGroupInterface;
use Ep\Tests\App\Middleware\TestMiddle1;
use Ep\Tests\App\Middleware\TestMiddle2;

final class FrontMiddleGroup implements MiddlewareGroupInterface
{
    public function getMiddlewares(): array
    {
        return [
            TestMiddle1::class,
            TestMiddle2::class
        ];
    }
}
