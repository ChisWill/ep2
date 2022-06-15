<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\ScanService;
use Ep\Console\Contract\RequestInterface;
use Ep\Console\Contract\ResponseInterface;
use Ep\Console\Trait\Renderer;
use Symfony\Component\Console\Input\InputOption;

final class ScanCommand
{
    use Renderer;

    public function __construct(private ScanService $service)
    {
        $this->define('index')
            ->addOption('ns', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The extra namespace to scan')
            ->addOption('ignore', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The pattern to ignore files')
            ->setDescription('Scan root path to generate annotation cache');
    }

    public function index(RequestInterface $request): ResponseInterface
    {
        $this->service
            ->load($request)
            ->scan();

        return $this->success();
    }
}
