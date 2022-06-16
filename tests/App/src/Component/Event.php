<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Web\Event\AfterRequest;
use Ep\Web\Event\BeforeRequest;
use Ep\Web\Event\EndBody;
use Psr\Http\Message\ServerRequestInterface;

final class Event
{
    public function before(BeforeRequest $beforeRequest)
    {
        $request = $beforeRequest->getRequest();
    }

    public function after(AfterRequest $afterRequest)
    {
        $request = $afterRequest->getRequest();
        $resonse = $afterRequest->getResponse();
    }

    public function endBody(EndBody $endBody)
    {
        $view = $endBody->getView();
        echo $view->renderPartial('/index/index', ['message' => 'end body event message']);
    }
}
