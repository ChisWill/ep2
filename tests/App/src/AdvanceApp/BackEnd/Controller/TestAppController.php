<?php

declare(strict_types=1);

namespace Ep\Tests\App\AdvanceApp\BackEnd\Controller;

use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Tests\App\Component\Controller;
use Ep\Traits\WebService;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;

#[Route('at')]
class TestAppController extends Controller
{
    // use WebService;

    #[Inject]
    private Service $service;

    #[Route('tr')]
    public function testRouteAction()
    {
        return $this->string((string) mt_rand(100, 200));
    }

    public function viewAction(ServerRequestInterface $req)
    {
        return $this->render('view');
    }
}
