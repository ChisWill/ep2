<?php

declare(strict_types=1);

namespace Ep\Console\Contract;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface FactoryInterface
{
    public function createRequest(InputInterface $input = null): RequestInterface;

    public function createResponse(OutputInterface $output = null): ResponseInterface;
}
