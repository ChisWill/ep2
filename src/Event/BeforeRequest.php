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

    /**
     * @param mixed $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
