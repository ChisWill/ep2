<?php

declare(strict_types=1);

namespace Ep\Base;

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;

final class Env
{
    private RepositoryInterface $repository;

    public function __construct(
        private string $rootPath
    ) {
        $this->repository = $this->createRepository();

        Dotenv::create($this->repository, $rootPath)->safeLoad();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        switch ($value = $this->repository->get($key)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
            case null:
                return $default;
            default:
                return $value;
        }
    }

    private function createRepository(): RepositoryInterface
    {
        return RepositoryBuilder::createWithDefaultAdapters()
            ->immutable()
            ->make();
    }
}
