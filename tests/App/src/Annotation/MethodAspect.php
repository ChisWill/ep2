<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;
use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\HandlerInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final class MethodAspect implements AspectInterface
{
    public function __construct()
    {
    }

    public function handle(HandlerInterface $handler): mixed
    {
        t(self::class);

        $result = $handler->handle();

        t(self::class);

        return $result;
    }
}
