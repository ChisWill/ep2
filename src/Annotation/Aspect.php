<?php

declare(strict_types=1);

namespace Ep\Annotation;

use Ep;
use Ep\Contract\AnnotationInterface;
use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Closure;
use ReflectionFunction;
use Reflector;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Aspect implements AnnotationInterface
{
    private array $class = [];

    public function __construct(array $values)
    {
        $this->normalize($values);
    }

    private function normalize(array $values): void
    {
        if (isset($values['value'])) {
            $this->class[$values['value']] = [];
        } else {
            $classes = (array) ($values['class'] ?? null);
            foreach ($classes as $key => $value) {
                if (is_string($key)) {
                    $this->class[$key] = (array) $value;
                } else {
                    $this->class[$value] = [];
                }
            }
        }
    }

    /**
     * @param  ReflectionFunction $reflector
     */
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
