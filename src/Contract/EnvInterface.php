<?php

declare(strict_types=1);

namespace Ep\Contract;

interface EnvInterface
{
    public function getRootPath(): string;

    public function get(string $key, mixed $default = null): mixed;
}
