<?php

declare(strict_types=1);

use Ep\Base\RouteCollection;
use Ep\Facade\Route;
use Ep\Tests\App\Controller\StateController;

Route::get('/p', [StateController::class, 'ping'])->name('ping');

Route::group('/try',  function (RouteCollection $route) {
    $route->get('/{action:[a-zA-Z][\w-]*}', 'test/<action>')->name('all');
    $route->group('/again', function (RouteCollection $route) {
        $route->get('/{action:[a-zA-Z][\w-]*}', 'test/again/<action>');
    })->name('a');
})->name('t');
