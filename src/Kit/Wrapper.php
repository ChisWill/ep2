<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\HandlerInterface;
use Closure;

final class Wrapper
{
    /**
     * @param AspectInterface[] $aspects
     */
    public function aspect(array $aspects, Closure $callback): HandlerInterface
    {
        krsort($aspects);
        $handler = $this->createHandlerFromClosure($callback);
        foreach ($aspects as $aspect) {
            $handler = $this->createHandlerFromAspect($aspect, $handler);
        }
        return $handler;
    }

    private function createHandlerFromClosure(Closure $callback): HandlerInterface
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

    private function createHandlerFromAspect(AspectInterface $aspect, HandlerInterface $handler): HandlerInterface
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
