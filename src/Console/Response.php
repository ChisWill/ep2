<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Console\Contract\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class Response implements ResponseInterface
{
    public function __construct(private OutputInterface $output)
    {
    }

    private int $code = Command::SUCCESS;

    public function setCode(int $code): ResponseInterface
    {
        $this->code = $code;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string|iterable $messages, int $level = 0): void
    {
        $this->output->write($messages, false, $level);
    }

    /**
     * {@inheritDoc}
     */
    public function writeln(string|iterable $messages, int $level = 0): void
    {
        $this->output->write($messages, true, $level);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(int $level): void
    {
        $this->output->setVerbosity($level);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritDoc}
     */
    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }
}
