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
use Ep\Traits\WebServiceTrait;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Profiler\ProfilerInterface;

#[Route('at')]
class TestAppController
{
    use WebServiceTrait;

    #[Inject]
    private Service $service;

    #[Route('tr')]
    public function testRouteAction()
    {
        tt(str_starts_with('ab', ''));
        return $this->string((string) mt_rand(100, 200));
    }

    #[BeforeAction]
    public function before()
    {
        tt(self::class, 'ok');
        $parent = new CompositeContainer;
        $parent->attach(Ep::getDi());
        $new = new Container(ContainerConfig::create()
            ->withDefinitions([
                CarFactory::class => [
                    'class' => CarFactory::class,
                    '$carType' => BMW::class
                ],
                CarInterface::class => [
                    'class' => BMW::class,
                    '$model' => 'X5'
                ]
            ]));

        $parent->attach($new);

        $r = $parent->get(ProfilerInterface::class);
        tt($r);

        return true;
    }

    public function viewAction(ServerRequestInterface $req)
    {
        return $this->render('view');
    }
}
