<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

interface ApplicationInterface
{
    public static function getDiProviderName(): ?string;
}
