<?php

declare(strict_types=1);

namespace Ep\Web\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AfterRequest
{
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response
    ) {
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
