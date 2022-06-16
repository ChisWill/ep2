<?php

declare(strict_types=1);

use Ep\Facade\Route;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

Route::name('ping')->get('/p', function (ServerRequestInterface $request, ResponseFactoryInterface $factory) {
    $res = $factory->createResponse();
    $res->getBody()->write('pong');
    return $res;
});

Route::name('t-')->group('/try', function () {
    Route::name('a-')->group('/again', function () {
        Route::get('/{action:[a-zA-Z][\w-]*}', 'test/<action>')->name('child');
    });

    Route::name('parent')->get('/{ctrl:[\w]+}/{action:[a-zA-Z][\w-]*}', '<ctrl>/<action>');
});
