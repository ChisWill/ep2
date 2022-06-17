<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Base\Contract\EventListenerInterface;
use Ep\Tests\App\Controller\DemoController;
use Ep\Web\Event\AfterRequest;
use Ep\Web\Event\BeforeRequest;
use Ep\Web\Event\EndBody;

final class EventListener implements EventListenerInterface
{
    public function getListeners(): array
    {
        return [
            DemoController::class => [
                function (DemoController $event) {
                    tt(__DIR__, $event::class);
                }
            ],
            BeforeRequest::class => [
                [EventHandler::class, 'before']
            ],
            AfterRequest::class => [
                [EventHandler::class, 'after']
            ],
            EndBody::class => [
                [EventHandler::class, 'endBody']
            ]
        ];
    }
}
