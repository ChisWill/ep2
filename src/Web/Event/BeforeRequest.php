<?php

declare(strict_types=1);

namespace Ep\Web\Event;

use Psr\Http\Message\ServerRequestInterface;

final class BeforeRequest
{
    public function __construct(
        private ServerRequestInterface $request
    ) {
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
