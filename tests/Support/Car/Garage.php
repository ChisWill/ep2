<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Car;

final class Garage
{
    public string $area = 'North';

    private array $cars;

    public function setCar(CarInterface $car): void
    {
        $this->cars[] = $car;
    }
}
