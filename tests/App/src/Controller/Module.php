<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Web\Module as WebModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Module extends WebModule
{
    public function __construct()
    {
        $this->setMiddlewares([]);
    }

    public function before(ServerRequestInterface $request): bool|ResponseInterface
    {
        return true;
    }

    public function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}