<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use RuntimeException;

final class Service
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function getTables(string $prefix = ''): array
    {
        $tables = $this->db->getSchema()->getTableNames();
        if ($prefix) {
            $tables = array_filter($tables, static fn ($name): bool => str_starts_with($name, $prefix));
        }
        return $tables;
    }

    /**
     * @throws RuntimeException
     */
    public function getDDL(string $tableName): string
    {
        switch ($this->db->getName()) {
            case 'mysql':
                $sql = <<<SQL
SHOW CREATE TABLE `{$tableName}`;
SQL;
                $field = 'Create Table';
                break;
            case 'sqlite':
                $sql = <<<SQL
SELECT `sql` FROM `sqlite_master` WHERE `type`='table' AND tbl_name='{$tableName}'
SQL;
                $field = 'sql';
                break;
            default:
                throw new RuntimeException('Unsupported DB driver "' . $this->db->getName() . '".');
        }

        $result = Query::find($this->db)
            ->createCommand()
            ->setRawSql($sql)
            ->queryOne();

        if (!$result) {
            throw new RuntimeException('The table name "' . $tableName . '" is not exists.');
        }

        return $result[$field];
    }
}
