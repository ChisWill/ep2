<?php

declare(strict_types=1);

namespace Ep\Tests\App\AdvanceApp\BackEnd\Controller;

use Ep;
use Ep\Attribute\BeforeAction;
use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Tests\Support\Car\BMW;
use Ep\Tests\Support\Car\CarInterface;
use Ep\Tests\Support\Car\Garage;
use Ep\Tests\Support\Car\Wheel;
use Ep\Tests\Support\Car\WheelInterface;
use Ep\Traits\WebService;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Profiler\ProfilerInterface;

#[Route('at')]
class TestAppController
{
    use WebService;

    #[Inject]
    private Service $service;

    public function __construct()
    {
    }

    #[Route('tr')]
    public function testRoute()
    {
        tt(str_starts_with('ab', ''));
        return $this->string((string) mt_rand(100, 200));
    }

    private function before()
    {
        return true;
    }

    public function view(ServerRequestInterface $req)
    {
        return $this->render('view');
    }
}
