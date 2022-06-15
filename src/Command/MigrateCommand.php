<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\MigrateService;
use Ep\Console\Contract\ConsoleRequestInterface;
use Ep\Console\Contract\ConsoleResponseInterface;
use Ep\Console\Trait\ConsoleService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand
{
    use ConsoleService;

    public function __construct(private MigrateService $service)
    {
        $this->define('create')
            ->addArgument('name', InputArgument::REQUIRED, 'Migration name')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->setDescription('Create an empty migration');

        $this
            ->define('init')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Table prefix')
            ->addOption('data', null, InputOption::VALUE_NONE, 'Whether initialize table data')
            ->setDescription('Initialize all tables');

        $this
            ->define('list')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->setDescription('Print list of all migrations');

        $this
            ->define('up')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migrations to apply')
            ->setDescription('Execute all new migrations');

        $this
            ->define('down')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to downgrade')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Whether downgrade all migration history')
            ->setDescription('Rollback last migration');
    }

    public function create(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->create($request->getArgument('name'));

        return $this->success();
    }

    public function init(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->init();

        return $this->success();
    }

    public function list(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->list();

        return $this->success();
    }

    public function up(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->up();

        return $this->success();
    }

    public function down(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->down();

        return $this->success();
    }
}
