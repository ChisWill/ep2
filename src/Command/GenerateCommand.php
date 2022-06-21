<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\GenerateService;
use Ep\Console\Contract\RequestInterface;
use Ep\Console\Contract\ResponseInterface;
use Ep\Console\Trait\Renderer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class GenerateCommand
{
    use Renderer;

    public function __construct(private GenerateService $service)
    {
        $this->define('key')->setDescription('Generate secret key');

        $this
            ->define('model')
            ->addArgument('table', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Table name')
            ->addOption('app', null, InputOption::VALUE_REQUIRED, 'App name')
            ->setDescription('Generate model');
    }

    public function key(): ResponseInterface
    {
        $this->service->createKey();

        return $this->success();
    }

    public function model(RequestInterface $request): ResponseInterface
    {
        $service = $this->service->load($request);

        foreach ($request->getArgument('table') as $table) {
            $service->generateModel($table);
        }
        return $this->success();
    }
}
