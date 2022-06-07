<?php

declare(strict_types=1);

namespace Ep\Tests\App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TimeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);

        $response = $handler->handle($request);

        $response->getBody()->write('<br>' . (microtime(true) - $start) * 1000 . 'ms');

        return $response;
    }
}
