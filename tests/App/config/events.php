<?php

declare(strict_types=1);

use Ep\Base\Event\AfterRequest;
use Ep\Base\Event\BeforeRequest;
use Ep\Tests\App\Component\Event;
use Ep\Tests\App\Controller\DemoController;
use Ep\Web\Event\EndBody;

return [
    DemoController::class => [
        function (DemoController $event) {
            echo $event->id;
        }
    ],
    BeforeRequest::class => [
        [Event::class, 'before']
    ],
    AfterRequest::class => [
        [Event::class, 'after']
    ],
    EndBody::class => [
        [Event::class, 'endBody']
    ]
];
