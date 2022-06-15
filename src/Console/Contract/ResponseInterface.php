<?php

declare(strict_types=1);

namespace Ep\Console\Contract;

interface ResponseInterface
{
    public function setCode(int $code): self;

    public function getCode(): int;

    public function write(string|iterable $messages, int $level = 0): void;

    public function writeln(string|iterable $messages, int $level = 0): void;

    public function setVerbosity(int $level): void;

    public function getVerbosity(): int;

    public function isQuiet(): bool;

    public function isVerbose(): bool;

    public function isVeryVerbose(): bool;

    public function isDebug(): bool;
}
