<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Console\Contract\FactoryInterface;
use Ep\Console\Contract\RequestInterface;
use Ep\Console\Contract\ResponseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Factory implements FactoryInterface
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output
    ) {
    }

    public function createRequest(InputInterface $input = null): RequestInterface
    {
        return new Request($input ?? $this->input);
    }

    public function createResponse(OutputInterface $output = null): ResponseInterface
    {
        return new Response($output ?? $this->output);
    }
}
