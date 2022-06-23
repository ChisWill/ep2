<?php

declare(strict_types=1);

use Ep\Facade\Route;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

Route::name('ping')
    ->get('/p')
    ->action(function (ServerRequestInterface $request, ResponseFactoryInterface $factory) {
        $res = $factory->createResponse();
        $res->getBody()->write('pong');
        return $res;
    });

Route::name('t-')->group('/try', function () {
    Route::name('a-')->group('/group', function () {
        Route::get('/{action:[a-zA-Z][\w-]*}')
            ->name('child')
            ->action('test/<action>');
    });

    Route::name('parent')
        ->get('/{ctrl:[\w]+}/{action:[a-zA-Z][\w-]*}')
        ->action('<ctrl>/<action>');
});
