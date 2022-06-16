<?php

declare(strict_types=1);

namespace Ep\Tests\App\AdvanceApp\BackEnd\Controller;

use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Base\Constant;
use Ep\Web\Trait\Renderer;
use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;

#[Route('ta')]
class TestAppController
{
    use Renderer;

    #[Inject]
    private Service $service;

    #[Route('tr')]
    public function testRoute()
    {
        return $this->string((string) mt_rand(100, 200));
    }

    public function view(ServerRequestInterface $request)
    {
        $title = 'test title';
        $controller = $request->getAttribute(Constant::REQUEST_CONTROLLER);
        $action = $request->getAttribute(Constant::REQUEST_ACTION);
        return $this->render('view', compact('title', 'controller', 'action'));
    }
}
