<?php

declare(strict_types=1);

namespace Ep\Event;

final class AfterRequest
{
    public function __construct(
        private mixed $request,
        private mixed $response
    ) {
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
