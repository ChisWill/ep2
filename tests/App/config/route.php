<?php

declare(strict_types=1);

use Ep\Base\RouteCollection;
use Ep\Facade\Route;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

Route::get('/p', function (ServerRequestInterface $request, ResponseFactoryInterface $factory) {
    $res = $factory->createResponse();
    $res->getBody()->write('pong');
    return $res;
})
    ->name('ping');

Route::group('/try', function (RouteCollection $route) {
    $route
        ->group('/again', function (RouteCollection $route) {
            $route
                ->get('/{action:[a-zA-Z][\w-]*}', 'test/<action>')
                ->name('child');
        })
        ->name('a-');
    $route
        ->get('/{ctrl:[\w]+}/{action:[a-zA-Z][\w-]*}', '<ctrl>/<action>')
        ->name('parent');
})
    ->name('t-');
