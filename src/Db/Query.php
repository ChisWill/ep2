<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Ep\Helper\Batch;
use Ep\Widget\Paginator;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Query\Query as YiiQuery;
use InvalidArgumentException;

final class Query extends YiiQuery
{
    use QueryTrait;

    public static function find(ConnectionInterface $db = null): Query
    {
        return new Query($db ?? Ep::getDb());
    }

    public function insert(string $table, array|Query $columns): int
    {
        if (!$columns) {
            return 0;
        }
        return $this->createCommand()
            ->insert($table, $columns)
            ->execute();
    }

    public function batchInsert(string $table, array $columns, iterable $rows): int
    {
        if (!$columns || !$rows) {
            return 0;
        }
        return $this->createCommand()
            ->batchInsert($table, $columns, $rows)
            ->execute();
    }

    public function update(string $table, array $columns, array|string $condition = '', array $params = []): int
    {
        if (!$columns || !$condition) {
            return 0;
        }
        return $this->createCommand()
            ->update($table, $columns, $condition, $params)
            ->execute();
    }

    public function upsert(string $table, array|Query $insertColumns, array|bool $updateColumns = true, array $params = []): int
    {
        if (!$insertColumns) {
            return 0;
        }
        return $this->createCommand()
            ->upsert($table, $insertColumns, $updateColumns, $params)
            ->execute();
    }

    public function delete(string $table, array|string $condition = '', array $params = []): int
    {
        if (!$condition) {
            return 0;
        }
        return $this->createCommand()
            ->delete($table, $condition, $params)
            ->execute();
    }

    public function increment(string $table, array $columns, array|string $condition = '', array $params = []): int
    {
        if (!$columns || !$condition) {
            return 0;
        }
        foreach ($columns as $field => &$value) {
            if (is_numeric($value)) {
                $value = new Expression("`{$field}` + {$value}");
            }
        }
        return $this->update($table, $columns, $condition, $params);
    }

    /**
     * @param  callable[] $callbacks 最后一个参数如果是字符串，表示主键字段名称
     * 
     * @throws InvalidArgumentException
     */
    public function reduce(int &$startId = 0, ...$callbacks): array
    {
        $count = count($callbacks);
        if ($count === 0 || !is_callable($callbacks[0])) {
            throw new InvalidArgumentException('It must be at least one callback.');
        }

        if (is_string($callbacks[$count - 1])) {
            $primaryKey = $callbacks[$count - 1];
            array_pop($callbacks);
        } else {
            $primaryKey = ActiveRecord::PK;
        }

        return Batch::reduce($this->getBatchProducer($primaryKey, $startId), ...$callbacks);
    }

    public function nextPage(int $startId, int $pageSize = 10, string $primaryKey = 'id'): array
    {
        return (new Paginator($this))->next($startId, $pageSize, $primaryKey);
    }
}
