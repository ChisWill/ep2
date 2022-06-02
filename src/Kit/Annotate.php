<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Constant;
use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\Attribute\ConfigureInterface;
use Ep\Contract\Attribute\ProcessInterface;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Attribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class Annotate
{
    private Injector $injector;

    public function __construct(
        private ContainerInterface $container,
        private CacheInterface $cache,
        private Wrapper $wrapper
    ) {
        $this->injector = new Injector($container);
    }

    public function class(object $instance): ReflectionClass
    {
        $reflectionClass = new ReflectionClass($instance);
        foreach ($reflectionClass->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if ($attribute instanceof ProcessInterface) {
                $attribute->process($instance, $reflectionClass);
            }
        }
        return $reflectionClass;
    }

    public function property(object $instance, array $arguments = []): void
    {
        foreach ($this->getProperties($this->class($instance)) as $reflectionProperty) {
            foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
                $attribute = $reflectionAttribute->newInstance();
                if ($attribute instanceof ProcessInterface) {
                    $attribute->process($instance, $reflectionProperty, $arguments);
                }
            }
        }
    }

    public function method(object $instance, string $method, array $arguments = []): mixed
    {
        $handler = fn (): mixed => $this->injector->invoke([$instance, $method], $arguments);

        $aspects = [];
        foreach ((new ReflectionMethod($instance, $method))->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if ($attribute instanceof AspectInterface) {
                $aspects[] = $attribute;
            }
        }
        if ($aspects) {
            return $this->wrapper->aspect($aspects, $handler)->handle();
        } else {
            return $handler();
        }
    }

    public function cache(array $classList, callable $callback = null): void
    {
        $data = [];
        $setData = static function (array $attributes, string $class, int $type, string $name = null) use (&$data): void {
            foreach ($attributes as $attribute) {
                /** @var ReflectionAttribute $attribute */
                $instance = $attribute->newInstance();
                if ($instance instanceof ConfigureInterface) {
                    switch ($type) {
                        case Attribute::TARGET_CLASS:
                            $data[$attribute->getName()][$class][$type] = $instance->getValues();
                            break;
                        case Attribute::TARGET_PROPERTY:
                        case Attribute::TARGET_METHOD:
                            $data[$attribute->getName()][$class][$type][] = ['target' => $name] + $instance->getValues();
                            break;
                    }
                }
            }
        };

        foreach ($classList as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            $setData($reflectionClass->getAttributes(), $class, Attribute::TARGET_CLASS);

            foreach ($reflectionClass->getProperties() as $property) {
                $setData($property->getAttributes(), $class, Attribute::TARGET_PROPERTY, $property->getName());
            }

            foreach ($reflectionClass->getMethods() as $method) {
                $setData($method->getAttributes(), $class, Attribute::TARGET_METHOD, $method->getName());
            }

            if (is_callable($callback)) {
                call_user_func($callback, $class);
            }
        }

        $this->cache->set(Constant::CACHE_ATTRIBUTE_DATA, $data, 86400 * 365 * 100);
    }

    /**
     * @return ReflectionProperty[]
     */
    private function getProperties(ReflectionClass $reflectionClass): array
    {
        $parentClass = $reflectionClass->getParentClass();
        $properties = $parentClass === false ? [] : $this->getProperties($parentClass);

        return array_merge($properties, $reflectionClass->getProperties());
    }
}
