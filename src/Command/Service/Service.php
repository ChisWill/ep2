<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Attribute\Inject;
use Ep\Console\Contract\ConsoleRequestInterface;
use Ep\Kit\Util;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

abstract class Service
{
    #[Inject]
    private Util $util;

    protected ConsoleRequestInterface $request;

    final public function load(ConsoleRequestInterface $request): static
    {
        $new = clone $this;
        $new->request = $request;
        $new->initDefaultOptions();
        $new->configure();
        return $new;
    }

    protected string $userRootNamespace;
    protected array $defaultOptions;

    private function initDefaultOptions(): void
    {
        $options = $this->request->getOptions();

        $this->userRootNamespace = $options['common']['userRootNamespace'];
        if (!empty($options['app'])) {
            $this->defaultOptions = $options['apps'][$options['app']][$this->getId()] ?? [];
        } else {
            $this->defaultOptions = $options[$this->getId()] ?? [];
        }
    }

    protected ?ConnectionInterface $db = null;

    protected function getDb(): Connection
    {
        if ($this->db === null) {
            $db = $this->request->getOption('db') ?? $this->defaultOptions['db'] ?? $this->request->getOption('common.db') ?? null;
            try {
                $this->db = Ep::getDb($db);
            } catch (NotFoundExceptionInterface $e) {
                $this->error(sprintf('The db "%s" is invalid.', $db));
            }
        }
        return $this->db;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getAppPath(): string
    {
        return $this->util->getAppPath($this->userRootNamespace);
    }

    protected function getClassNameByFile(string $file): string
    {
        return $this->util->getClassNameByFile($this->userRootNamespace, $file);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function error(string $message): void
    {
        throw new InvalidArgumentException($message);
    }

    private function getId(): string
    {
        return lcfirst(basename(str_replace('\\', '/', static::class), 'Service'));
    }

    abstract protected function configure(): void;
}
