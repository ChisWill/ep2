<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Attribute\Inject;
use Ep\Console\Contract\RequestInterface;
use Ep\Kit\Util;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;
use LogicException;

abstract class Service
{
    #[Inject]
    private Util $util;

    private RequestInterface $request;

    final public function load(RequestInterface $request): static
    {
        $new = clone $this;
        $new->request = $request;
        $new->loadDefaultOptions();
        $new->configure();
        return $new;
    }

    protected function getRequest(): RequestInterface
    {
        if (!isset($this->request)) {
            throw new LogicException('Must call method "' . static::class . '::load()" first.');
        }
        return $this->request;
    }

    protected string $userRootNamespace;
    protected array $defaultOptions;

    private function loadDefaultOptions(): void
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
            $db = $this->defaultOptions['db'] ?? $this->request->getOption('common.db') ?? null;
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
