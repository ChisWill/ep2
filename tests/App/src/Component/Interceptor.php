<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Base\Contract\InterceptorInterface;
use Ep\Tests\App\Middleware\RootMiddleware;

class Interceptor implements InterceptorInterface
{
    public function includePath(): array
    {
        return [
            '/a/p' => RootMiddleware::class,
        ];
    }

    public function excludePath(): array
    {
        return [];
    }
}
