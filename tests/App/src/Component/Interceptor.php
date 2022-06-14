<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Contract\InterceptorInterface;
use Ep\Tests\App\Middleware\RootMiddleware;

class Interceptor implements InterceptorInterface
{
    public function includePath(): array
    {
        return [
            '/' => RootMiddleware::class,
        ];
    }

    public function excludePath(): array
    {
        return [];
    }
}
