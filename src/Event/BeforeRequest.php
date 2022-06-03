<?php

declare(strict_types=1);

namespace Ep\Event;

final class BeforeRequest
{
    public function __construct(
        private mixed $request,
        private mixed $response
    ) {
    }

    public function setRequest(mixed $request): void
    {
        $this->request = $request;
    }

    public function getRequest(): mixed
    {
        return $this->request;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
