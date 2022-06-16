<?php

declare(strict_types=1);

namespace Ep\Tests\App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TestMiddle1 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        t(self::class);

        $response = $handler->handle($request);

        t(self::class);

        return $response;
    }
}
