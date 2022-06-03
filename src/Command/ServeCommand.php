<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\ServeService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputOption;

final class ServeCommand extends Command
{
    public function __construct(private ServeService $service)
    {
        $this
            ->createDefinition('index')
            ->addOption('address', null, InputOption::VALUE_REQUIRED, 'Host to serve at')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port to serve at')
            ->addOption('docroot', null, InputOption::VALUE_REQUIRED, 'Document root to serve from')
            ->addOption('router', null, InputOption::VALUE_REQUIRED, 'Path to router script')
            ->setDescription('Runs PHP built-in web server');
    }

    public function indexAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->serve();

        return $this->success();
    }
}
