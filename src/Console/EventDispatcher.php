<?php

declare(strict_types=1);

namespace Ep\Console;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

final class EventDispatcher implements SymfonyEventDispatcherInterface
{
    public function __construct(private PsrEventDispatcherInterface $dispatcher)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event, string $eventName = null): object
    {
        return $this->dispatcher->dispatch($event);
    }
}
