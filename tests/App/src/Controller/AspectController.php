<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Attribute\Middleware;
use Ep\Attribute\Route;
use Ep\Tests\App\Annotation\BeforeAfterAspect;
use Ep\Tests\App\Annotation\MethodAspect;
use Ep\Tests\App\Middleware\ModuleMiddleware;
use Ep\Web\Trait\WebService;
use Psr\Http\Message\ServerRequestInterface;

#[Route('a')]
#[Middleware([ModuleMiddleware::class])]
#[BeforeAfterAspect]
class AspectController
{
    use WebService;

    #[Route('p')]
    #[MethodAspect]
    public function ping(ServerRequestInterface $request)
    {
        t('pong' . $request->getUri()->getPath());
        return $this->string('pong');
    }
}
