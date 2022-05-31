<?php

declare(strict_types=1);

namespace Ep\Event;

final class AfterRequest
{
    public function __construct(private mixed $request, private mixed $response)
    {
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
