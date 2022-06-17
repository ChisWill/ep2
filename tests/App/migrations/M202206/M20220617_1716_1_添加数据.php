<?php

declare(strict_types=1);

use Ep\Command\Helper\MigrateBuilder;
use Ep\Base\Contract\MigrateInterface;

final class M20220617_1716_1_添加数据 implements MigrateInterface
{
    public static function getName(): string
    {
        return '添加数据';
    }

    public function up(MigrateBuilder $builder): void
    {
        $builder->batchInsert('class', ['id', 'school_id', 'name'], [
            ['6', '3', 'Three'],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->delete('class', [
            'id' => 6
        ]);
    }
}
