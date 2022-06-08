<?php

declare(strict_types=1);

namespace Ep\Tests\App\AdvanceApp\BackEnd\Controller;

use Ep\Attribute\BeforeAction;
use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Traits\WebServiceTrait;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;

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
        return true;
    }

    public function viewAction(ServerRequestInterface $req)
    {
        return $this->render('view');
    }
}
