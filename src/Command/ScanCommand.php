<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\ScanService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputOption;

final class ScanCommand extends Command
{
    public function __construct(private ScanService $service)
    {
        $this
            ->createDefinition('index')
            ->addOption('ns', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The extra namespace to scan')
            ->addOption('ignore', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The pattern to ignore files')
            ->setDescription('Scan root path to generate annotation cache');
    }

    public function indexAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->scan();

        return $this->success();
    }
}
