<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Base\Contract\MiddlewareGroupInterface;
use Ep\Tests\App\Middleware\TestMiddle1;
use Ep\Tests\App\Middleware\TestMiddle2;

final class FrontMiddleGroup implements MiddlewareGroupInterface
{
    public static function getMiddlewares(): array
    {
        return [
            TestMiddle1::class,
            TestMiddle2::class
        ];
    }
}
