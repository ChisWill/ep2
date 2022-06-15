<?php

declare(strict_types=1);

namespace Ep\Attribute\Contract;

interface ConfigureInterface
{
    public function getValues(): array;
}
