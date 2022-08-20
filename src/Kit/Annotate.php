<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Attribute\Contract\AspectInterface;
use Ep\Attribute\Contract\ConfigureInterface;
use Ep\Attribute\Contract\ProcessInterface;
use Ep\Base\Constant;
use Ep\Base\Contract\HandlerInterface;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Attribute;
use Closure;
use Error;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class Annotate
{
    private Injector $injector;

    public function __construct(
        private CacheInterface $cache,
        ContainerInterface $container
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
        foreach (array_merge(
            (new ReflectionClass($instance))->getAttributes(),
            (new ReflectionMethod($instance, $method))->getAttributes()
        )  as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if ($attribute instanceof AspectInterface) {
                $aspects[] = $attribute;
            }
        }
        if ($aspects) {
            return $this->wrapAspects($aspects, $handler)->handle();
        } else {
            return $handler();
        }
    }

    private ?array $cacheResult = null;

    public function getCache(string $attributeClass, string $targetClass = null, int|array $position = null): array
    {
        if ($this->cacheResult === null) {
            $this->cacheResult = $this->cache->get(Constant::CACHE_ATTRIBUTE_DATA) ?: [];
        }
        $classList = $this->cacheResult[$attributeClass] ?? [];
        if ($targetClass === null) {
            return $classList;
        }

        $posList = $classList[$targetClass] ?? [];
        if ($position === null) {
            return $posList;
        }

        $result = [];
        foreach ((array) $position as $pos) {
            $result = array_merge($result, $posList[$pos] ?? []);
        }
        return $result;
    }

    public function scan(array $classList, callable $callback = null): void
    {
        $data = [];
        $setData = static function (array $attributes, string $class, int $type, string $name = null) use (&$data): void {
            /** @var ReflectionAttribute[] $attributes */
            foreach ($attributes as $attribute) {
                try {
                    $instance = $attribute->newInstance();
                } catch (Error $e) {
                    continue;
                }
                if ($instance instanceof ConfigureInterface) {
                    switch ($type) {
                        case Attribute::TARGET_CLASS:
                            $data[$attribute->getName()][$class][$type] = $instance->getValues();
                            break;
                        case Attribute::TARGET_PROPERTY:
                        case Attribute::TARGET_METHOD:
                            $data[$attribute->getName()][$class][$type][] = [Constant::ATTRIBUTE_TARGET => $name] + $instance->getValues();
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

        $this->cache->set(Constant::CACHE_ATTRIBUTE_DATA, $data, Constant::CACHE_MAX_TIME);
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

    private function wrapAspects(array $aspects, Closure $callback): HandlerInterface
    {
        krsort($aspects);
        $handler = $this->wrapClosure($callback);
        foreach ($aspects as $aspect) {
            $handler = $this->wrapAspect($aspect, $handler);
        }
        return $handler;
    }

    private function wrapClosure(Closure $callback): HandlerInterface
    {
        return new class($callback) implements HandlerInterface
        {
            public function __construct(private Closure $callback)
            {
            }

            /**
             * {@inheritDoc}
             */
            public function handle(): mixed
            {
                return call_user_func($this->callback);
            }
        };
    }

    private function wrapAspect(AspectInterface $aspect, HandlerInterface $handler): HandlerInterface
    {
        return new class($aspect, $handler) implements HandlerInterface
        {
            public function __construct(
                private AspectInterface $aspect,
                private HandlerInterface $handler
            ) {
            }

            /**
             * {@inheritDoc}
             */
            public function handle(): mixed
            {
                return $this->aspect->handle($this->handler);
            }
        };
    }
}
