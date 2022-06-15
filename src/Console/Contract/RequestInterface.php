<?php

declare(strict_types=1);

namespace Ep\Console\Contract;

interface RequestInterface
{
    public function getRoute(): string;

    public function hasArgument(string $name): bool;

    public function getArgument(string $name): string|array|null;

    public function setArgument(string $name, string|array|null $value): void;

    public function getArguments(): array;

    public function setArguments(array $arguments): void;

    public function hasOption(string $name): bool;

    public function getOption(string $name): string|array|bool|null;

    public function setOption(string $name, string|array|bool|null $value): void;

    public function getOptions(): array;

    public function setOptions(array $options): void;
}
