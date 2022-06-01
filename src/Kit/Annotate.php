<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Constant;
use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\Attribute\ConfigureInterface;
use Ep\Contract\Attribute\ProcessInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Attribute;
use Ep\Contract\HandlerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionProperty;

final class Annotate
{
    private Injector $injector;

    public function __construct(
        private ContainerInterface $container,
        private CacheInterface $cache
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
        $callback = fn (): mixed => $this->injector->invoke([$instance, $method], $arguments);

        $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
        $aspects = [];
        foreach ($reflectionMethod->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if ($attribute instanceof AspectInterface) {
                $aspects[] = $attribute;
            }
        }
        if ($aspects) {
            return $this->wrapHandler($aspects, $callback)->handle();
        } else {
            return $callback();
        }
    }

    public function cache(array $classList, callable $callback = null): void
    {
        $configureData = [];

        $classList = ['Ep\Tests\App\Controller\TestController'];

        $setData = static function (array $attributes, string $class, int $type, string $name = null) use (&$configureData): void {
            foreach ($attributes as $attribute) {
                /** @var ReflectionAttribute $attribute */
                $instance = $attribute->newInstance();
                if ($instance instanceof ConfigureInterface) {
                    switch ($type) {
                        case Attribute::TARGET_CLASS:
                            $configureData[get_class($attribute)][$class][$type] = $instance->getValues();
                            break;
                        case Attribute::TARGET_PROPERTY:
                        case Attribute::TARGET_METHOD:
                            $configureData[get_class($attribute)][$class][$type][] = array_merge($instance->getValues(), ['target' => $name]);
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

            if ($callback !== null) {
                call_user_func($callback, $class);
            }
        }

        $this->cache->set(Constant::CACHE_ANNOTATION_CONFIGURE_DATA, $configureData, 86400 * 365 * 100);
    }

    /**
     * @return ReflectionProperty[]
     */
    private function getProperties(ReflectionClass $reflectionClass): array
    {
        $parentClass = $reflectionClass->getParentClass();
        $properties = $parentClass === false ? [] : $this->getProperties($parentClass);

        return ArrayHelper::index($reflectionClass->getProperties(), static fn (ReflectionProperty $property): string => $property->getName()) + $properties;
    }

    /**
     * @param AspectInterface[] $aspects
     */
    private function wrapHandler(array $aspects, callable $callback): HandlerInterface
    {
        $handler = $this->wrapClosure($callback);
        foreach ($aspects as $aspect) {
            $handler = $this->wrapAspect($this->injector->make($aspect));
        }
        return $handler;
    }

    public function process(object $instance, Reflector $reflector, array $arguments = []): mixed
    {
        krsort($this->class);
        $handler = $this->wrapClosure($reflector->getClosure());
        foreach ($this->class as $class => $args) {
            $handler = $this->wrapAspect(Ep::getInjector()->make($class, array_merge($arguments, $args)), $handler);
        }
        return $handler->handle();
    }

    private function wrapClosure(Closure $closure): HandlerInterface
    {
        return new class($closure) implements HandlerInterface
        {
            public function __construct(private Closure $closure)
            {
                $this->closure = $closure;
            }

            /**
             * {@inheritDoc}
             */
            public function handle(): mixed
            {
                return call_user_func($this->closure);
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
                $this->aspect = $aspect;
                $this->handler = $handler;
            }

            /**
             * {@inheritDoc}
             */
            public function handle(): mixed
            {
                return $this->aspect->process($this->handler);
            }
        };
    }
}
