<?php

declare(strict_types=1);

namespace Ep\Contract\Attribute;

interface ConfigureInterface
{
    public function getValues(): array;
}
