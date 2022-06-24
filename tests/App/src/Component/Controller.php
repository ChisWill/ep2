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
            'code' => 200,
            'message' => 'OK',
            'body' => $body
        ]);
    }

    protected function error(array|string $message, int $code = 500, array|string $body = []): ResponseInterface
    {
        return $this->json([
            'code' => $code,
            'message' => $message,
            'body' => $body
        ]);
    }
}
