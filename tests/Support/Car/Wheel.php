<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Car;

final class Wheel implements WheelInterface
{
    public function getRadius(): int
    {
        return 20;
    }
}
