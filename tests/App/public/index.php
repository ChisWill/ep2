<?php

declare(strict_types=1);

use Ep\Web\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$start = microtime(true);

Ep::create(dirname(__DIR__))
    ->ready(Application::class)
    ->run();

$end = microtime(true);

Ep::getLogger()->info(($end - $start) * 1000 . 'ms');
