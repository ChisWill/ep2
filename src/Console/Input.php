<?php

declare(strict_types=1);

namespace Ep\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

final class Input implements InputInterface
{
    private ArgvInput $input;

    public function __construct()
    {
        $this->input = new ArgvInput();
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstArgument(): ?string
    {
        return $this->input->getFirstArgument();
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameterOption(string|array $values, bool $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterOption(string|array $values, string|bool|int|float|array|null $default = false, bool $onlyParams = false): mixed
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    /**
     * {@inheritDoc}
     */
    public function bind(InputDefinition $definition): void
    {
        $this->input->bind($definition);
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): void
    {
        $this->input->validate();
    }

    private array $arguments = [];

    /**
     * {@inheritDoc}
     */
    public function getArguments(): array
    {
        return $this->arguments + $this->input->getArguments();
    }

    /**
     * {@inheritDoc}
     */
    public function getArgument(string $name): mixed
    {
        return $this->arguments[$name] ?? $this->input->getArgument($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setArgument(string $name, mixed $value): void
    {
        $this->arguments[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments) || $this->input->hasArgument($name);
    }

    private array $options = [];

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->options + $this->input->getOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? $this->input->getOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setOption(string $name, mixed $value): void
    {
        $this->options[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options) || $this->input->hasOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritDoc}
     */
    public function setInteractive(bool $interactive): void
    {
        $this->input->setInteractive($interactive);
    }

    public function __toString(): string
    {
        return $this->input->__toString();
    }
}
