<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep\Console\Service;
use Ep\Console\Contract\RequestInterface;
use Ep\Console\Trait\Renderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputOption;

class InitCommand
{
    use Renderer;

    public function __construct(private Service $service)
    {
        $this
            ->define('index')
            ->addArgument('name', null, 'your name')
            ->addOption('type', 't', InputOption::VALUE_NONE);
    }

    public function index(RequestInterface $request)
    {
        $message = 'Welcome Basic, ' . $request->getArgument('name');

        echo 'show over';

        return $this->success($message);
    }

    public function log(LoggerInterface $logger)
    {
        $logger->info('log info', ['act' => self::class]);

        return $this->success();
    }

    public function request(RequestInterface $request)
    {
        t([
            'route' => $request->getRoute(),
            'options' => $request->getOptions(),
            'argvs' => $request->getArguments()
        ]);

        return $this->success();
    }

    public function call(RequestInterface $request)
    {
        $this->service->call('init/table');

        return $this->success('call over');
    }

    public function table()
    {
        $this->service->renderTable([
            'name', 'id', 'age'
        ], [
            ['zs', 1, 33],
            ['fe', 31, 333],
            ['gvb', 51, 315],
        ]);

        return $this->success('table over');
    }

    public function progress()
    {
        $this->service->progress(function (ProgressBar $bar): void {
            $i = 0;
            while ($i++ < 50) {
                $bar->advance(2);
                usleep(30 * 1000);
            }
        });

        return $this->success();
    }

    public function echoArr()
    {
        $message = 'con';
        return $this->success(json_encode(compact('message')));
    }
}
