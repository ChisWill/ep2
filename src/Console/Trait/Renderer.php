<?php

declare(strict_types=1);

namespace Ep\Console\Trait;

use Ep\Attribute\Inject;
use Ep\Console\CommandDefinition;
use Ep\Console\Service;
use Ep\Console\Contract\ResponseInterface;
use Symfony\Component\Console\Command\Command;

trait Renderer
{
    #[Inject]
    private Service $__service;

    private array $__definitions = [];

    /**
     * @return CommandDefinition[]
     */
    public function __getDefinitions(): array
    {
        return $this->__definitions;
    }

    private function define(string $action): CommandDefinition
    {
        return $this->__definitions[$action] ??= new CommandDefinition();
    }

    private function success(string $message = ''): ResponseInterface
    {
        if ($message) {
            $this->__service->writeln($message);
        }

        return $this->__service->status(Command::SUCCESS);
    }

    private function error(string $message): ResponseInterface
    {
        $this->__service->writeln($message);

        return $this->__service->status(Command::FAILURE);
    }

    private function write(string $message = '', int $options = 0): void
    {
        $this->__service->write($message, $options);
    }

    private function writeln(string $message = '', int $options = 0): void
    {
        $this->__service->writeln($message, $options);
    }

    private function confirm(string $message, bool $default = false): bool
    {
        return $this->__service->confirm($message, $default);
    }
}
