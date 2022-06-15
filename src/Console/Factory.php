<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Console\Contract\ConsoleFactoryInterface;
use Ep\Console\Contract\ConsoleRequestInterface;
use Ep\Console\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Factory implements ConsoleFactoryInterface
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output
    ) {
    }

    public function createRequest(InputInterface $input = null): ConsoleRequestInterface
    {
        return new Request($input ?? $this->input);
    }

    public function createResponse(OutputInterface $output = null): ConsoleResponseInterface
    {
        return new Response($output ?? $this->output);
    }
}
