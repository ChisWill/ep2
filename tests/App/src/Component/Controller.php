<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Web\Trait\Renderer;
use Psr\Http\Message\ResponseInterface;

class Controller
{
    use Renderer;

    protected function success(array|string $body = []): ResponseInterface
    {
        return $this->json([
            'errno' => 0,
            'error' => 'OK',
            'body' => $body
        ]);
    }

    protected function error(array|string $error, int $errno = 500, array|string $body = []): ResponseInterface
    {
        return $this->json([
            'errno' => $errno,
            'error' => $error,
            'body' => $body
        ]);
    }
}
