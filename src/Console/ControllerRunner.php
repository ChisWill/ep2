<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Config;
use Ep\Base\ControllerRunner as BaseControllerRunner;

final class ControllerRunner extends BaseControllerRunner
{
    public function __construct(private Config $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerSuffix(): string
    {
        return $this->config->commandSuffix;
    }
}
