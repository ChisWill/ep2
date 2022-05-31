<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep\Base\Config;

interface EnvInterface
{
    public function getRootPath(): string;

    public function getConfig(): Config;

    public function get(string $key, mixed $default = null): mixed;
}
