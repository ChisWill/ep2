<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Car;

final class BMW implements CarInterface
{
    public string $model = 'X1';

    public function getSpeed(): int
    {
        return 200;
    }
}
