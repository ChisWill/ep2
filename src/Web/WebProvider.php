<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Tests\Support\Car\BMW;
use Ep\Tests\Support\Car\CarFactory;
use Ep\Tests\Support\Car\CarInterface;
use Ep\Tests\Support\Car\Garage;
use Psr\Container\ContainerInterface;
use Yiisoft\Di\ServiceProviderInterface;

final class WebProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            CarFactory::class => [
                'class' => CarFactory::class,
                '$carType' => BMW::class
            ],
            CarInterface::class => [
                'class' => BMW::class,
                '$model' => 'X5'
            ],
            Garage::class => [
                'class' => Garage::class,
                '$area' => 'South'
            ]
        ];
    }

    public function getExtensions(): array
    {
        return [
            Garage::class => function (ContainerInterface $container, Garage $garage) {
                $car = $container
                    ->get(CarFactory::class)
                    ->create();
                $garage->setCar($car);

                return $garage;
            }
        ];
    }
}
