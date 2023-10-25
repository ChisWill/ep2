<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ErrorHandler;
use Ep\Console\Contract\FactoryInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends SymfonyApplication
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output,
        private FactoryInterface $factory,
        private ErrorRenderer $errorRenderer
    ) {
        parent::__construct('Ep', Ep::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input ??= $this->input;
        $output ??= $this->output;

        ErrorHandler::create($this->errorRenderer)->register(
            $this->factory->createRequest($input)
        );

        return parent::run($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function extractNamespace(string $name, int $limit = null): string
    {
        $parts = explode('/', $name, -1);

        return ucfirst(implode('/', $limit === null ? $parts : array_slice($parts, 0, $limit)));
    }
}
