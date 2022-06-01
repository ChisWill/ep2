<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Attribute;
use Ep\Contract\Attribute\ProcessInterface;
use ReflectionClass;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class ClassAnnotation implements ProcessInterface
{
    /**
     * @param ReflectionClass $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): mixed
    {
        return $this;
    }
}
