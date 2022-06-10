<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Console\CommandLoader;
use Ep\Console\EventDispatcher;
use Ep\Console\Factory as ConsoleFactory;
use Ep\Contract\ConsoleFactoryInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Di\ServiceProviderInterface;

final class DiProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            ConsoleFactoryInterface::class => ConsoleFactory::class,
            CommandLoaderInterface::class => CommandLoader::class,
            EventDispatcherInterface::class => EventDispatcher::class,
            InputInterface::class => static fn (): InputInterface => new ArgvInput(null, null),
            OutputInterface::class => ConsoleOutput::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
