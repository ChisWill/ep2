<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Attribute\Middleware;
use Ep\Attribute\Route;
use Ep\Base\Contract\HandlerInterface;
use Ep\Tests\App\Annotation\BeforeAfterAspect;
use Ep\Tests\App\Annotation\MethodAspect;
use Ep\Tests\App\Component\FrontMiddleGroup;
use Ep\Tests\App\Middleware\ModuleMiddleware;
use Ep\Web\Trait\Renderer;
use Psr\Http\Message\ServerRequestInterface;

#[Route('a')]
#[Middleware([ModuleMiddleware::class, FrontMiddleGroup::class])]
#[BeforeAfterAspect]
class AspectController
{
    use Renderer;

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
}
