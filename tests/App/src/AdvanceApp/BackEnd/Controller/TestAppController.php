<?php

declare(strict_types=1);

namespace Ep\Tests\App\AdvanceApp\BackEnd\Controller;

use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Web\Trait\WebService;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;

#[Route('ta')]
class TestAppController
{
    use WebService;

    #[Inject]
    private Service $service;

    #[Route('tr')]
    public function testRoute()
    {
        return $this->string((string) mt_rand(100, 200));
    }

    public function view(ServerRequestInterface $req)
    {
        return $this->render('view');
    }
}
