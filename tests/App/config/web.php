<?php

declare(strict_types=1);

use Ep\Tests\App\Component\ServiceProvider;

return [
    'rootNamespace' => 'Ep\Tests\App',
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'env' => env('ENV'),
    'debug' => env('DEBUG'),
    'secretKey' => env('SECRET_KEY'),
    'di' => ServiceProvider::class,
    'events' => require('events.php'),
    'params' => require('params.php')
];
