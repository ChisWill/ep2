<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ApplicationInterface
{
    public static function getDiProviderName(): ?string;
}
