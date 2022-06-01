<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Web\Event\EndBody;
use Psr\Http\Message\ServerRequestInterface;

final class Event
{
    public function before(BeforeRequest $beforeRequest)
    {
        $request = $beforeRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
    }

    public function after(AfterRequest $afterRequest)
    {
        $request = $afterRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
    }

    public function endBody(EndBody $endBody)
    {
        $view = $endBody->getView();
        echo $view->renderPartial('/index/index', ['message' => 'end body event message']);
    }
}
