<?php

declare(strict_types=1);

use Ep\Tests\App\Component\DiProvider;

return [
    'rootNamespace' => 'Ep\Tests\App',
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'diProviderClass' => DiProvider::class,
    'env' => env('ENV'),
    'debug' => env('DEBUG'),
    'secretKey' => env('SECRET_KEY'),
    'params' => require('params.php')
];
