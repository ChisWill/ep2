<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Attribute\Route;
use Ep\Base\Contract\HandlerInterface;
use Ep\Tests\App\Annotation\CtrlBeforeAfterAspect;
use Ep\Tests\App\Annotation\MethodAspect;
use Ep\Tests\App\Component\FrontMiddleGroup;
use Ep\Tests\App\Middleware\ModuleMiddleware;
use Ep\Tests\App\Middleware\ModuleMiddleware2;
use Ep\Web\Trait\Middleware;
use Ep\Web\Trait\Renderer;
use Psr\Http\Message\ServerRequestInterface;

#[Route('a')]
#[CtrlBeforeAfterAspect]
class AspectController
{
    use Renderer, Middleware;

    public function __construct()
    {
        $this->middleware([ModuleMiddleware::class, ModuleMiddleware2::class])->only(['ping']);
        $this->middleware([FrontMiddleGroup::class])->except(['test']);
    }

    public function __around(ServerRequestInterface $request, HandlerInterface $handler)
    {
        t(__METHOD__);

        $response = $handler->handle();

        t(__METHOD__);

        return $response;
    }

    #[Route('p')]
    #[MethodAspect]
    public function ping(ServerRequestInterface $request)
    {
        t('pong' . $request->getUri()->getPath());
        return $this->string('pong');
    }

    #[MethodAspect]
    public function test()
    {
        return $this->string('test');
    }
}
