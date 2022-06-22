<?php

declare(strict_types=1);

namespace Ep\Attribute;

use Ep;
use Ep\Attribute\Contract\ProcessInterface;
use Attribute;
use Reflector;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Inject implements ProcessInterface
{
    private array $properties = [];

    public function __construct(mixed ...$properties)
    {
        $this->properties = $properties;
    }

    /**
     * @param ReflectionProperty $property
     */
    public function process(object $instance, Reflector $property, array $arguments = []): void
    {
        $className = $property->getType()->getName();

        $target = $this->getTargetFromArguments($arguments, $className) ?? Ep::getDi()->get($className);

        if ($this->properties) {
            $target = clone $target;
            foreach ($this->properties as $name => $value) {
                $targetProperty = new ReflectionProperty($target, $name);
                $targetProperty->setAccessible(true);
                $targetProperty->setValue($target, $value);
            }
        }

        $property->setAccessible(true);
        $property->setValue($instance, $target);
    }

    private function getTargetFromArguments(array $arguments, string $className): ?object
    {
        foreach ($arguments as $value) {
            if (is_object($value) && is_subclass_of($value, $className, false)) {
                return $value;
            }
        }
        return null;
    }
}
