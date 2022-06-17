<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Web\Event\AfterRequest;
use Ep\Web\Event\BeforeRequest;
use Ep\Web\Event\EndBody;
use Psr\Http\Message\ServerRequestInterface;

final class EventHandler
{
    public function before(BeforeRequest $beforeRequest)
    {
        $request = $beforeRequest->getRequest();
        if (str_starts_with($request->getUri()->getPath(), '/a/')) {
            t(__METHOD__);
        }
    }

    public function after(AfterRequest $afterRequest)
    {
        $request = $afterRequest->getRequest();
        $resonse = $afterRequest->getResponse();
        if (str_starts_with($request->getUri()->getPath(), '/a/')) {
            t(__METHOD__);
        }
    }

    public function endBody(EndBody $endBody)
    {
        $view = $endBody->getView();
        echo $view->renderPartial('/index/index', ['message' => 'The end body event message']);
    }
}
