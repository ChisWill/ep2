<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Car;

final class Car implements CarInterface
{
    public function __construct(private WheelInterface $wheel)
    {
    }

    public function getSpeed(): int
    {
        return 100;
    }
}
