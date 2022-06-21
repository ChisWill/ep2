<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Console\Service as ConsoleService;
use Ep\Kit\Annotate;
use Ep\Kit\Util;
use Symfony\Component\Console\Helper\ProgressBar;

final class ScanService extends Service
{
    public function __construct(
        private Annotate $annotate,
        private ConsoleService $consoleService,
        private Util $util
    ) {
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
    }

    public function scan(): void
    {
        $options = $this->getRequest()->getOptions();

        $classList = [];
        foreach (array_merge([$this->userRootNamespace], str_replace('/', '\\', $options['ns'])) as $rootNamespace) {
            $classList = array_merge($classList, $this->util->getClassList($rootNamespace, $options['ignore']));
        }

        $this->consoleService->progress(
            fn (ProgressBar $progressBar) => $this->annotate->scan(
                $classList,
                static fn () => $progressBar->advance()
            ),
            count($classList)
        );

        $this->consoleService->writeln();
    }
}
