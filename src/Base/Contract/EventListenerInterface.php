<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

interface EventListenerInterface
{
    public function getListeners(): array;
}
