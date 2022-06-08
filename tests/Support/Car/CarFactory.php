<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Car;

final class CarFactory
{
    public string $carType = Car::class;

    public function create(): CarInterface
    {
        return new $this->carType;
    }
}
