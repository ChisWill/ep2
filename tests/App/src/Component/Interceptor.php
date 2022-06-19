<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Web\Contract\InterceptorInterface;
use Ep\Tests\App\Middleware\RootMiddleware;
use Ep\Tests\App\Middleware\RootMiddleware2;

class Interceptor implements InterceptorInterface
{
    public function includePath(): array
    {
        return [
            '/a/' => [RootMiddleware::class, RootMiddleware2::class],
        ];
    }

    public function excludePath(): array
    {
        return [];
    }
}
