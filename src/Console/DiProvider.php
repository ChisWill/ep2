<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Console\Contract\ConsoleFactoryInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\ServiceProviderInterface;

final class DiProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Application
            Application::class => [
                'setAutoExit()' => [false],
                'setCommandLoader()' => [Reference::to(CommandLoaderInterface::class)],
                'setDispatcher()' => [Reference::to(EventDispatcherInterface::class)]
            ],
            ConsoleFactoryInterface::class => Factory::class,
            CommandLoaderInterface::class => CommandLoader::class,
            EventDispatcherInterface::class => EventDispatcher::class,
            // Input
            InputInterface::class => static fn (): InputInterface => new ArgvInput(null, null),
            // Output
            OutputInterface::class => ConsoleOutput::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
