<?php

declare(strict_types=1);

use Ep\Command\Helper\MigrateBuilder;
use Ep\Base\Contract\MigrateInterface;

final class Initialization implements MigrateInterface
{
    public static function getName(): string
    {
        return 'Initialization';
    }

    public function up(MigrateBuilder $builder): void
    {
        $builder->execute(<<<'DDL'
CREATE TABLE `class` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`school_id` integer NOT NULL,
	`name` varchar(50) NOT NULL
);
CREATE TABLE "school" (
	 "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	 "name" TEXT NOT NULL
);
CREATE TABLE `story` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`title` varchar(50) NOT NULL,
	`desc` varchar(100) DEFAULT '',
	`content` text
);
CREATE TABLE `student` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`class_id` integer NOT NULL,
	`name` varchar(50) NOT NULL,
	`password` varchar(100) NOT NULL,
	`age` smallint DEFAULT 0,
	`birthday` datetime,
	`sex` tinyint DEFAULT 0,
	`desc` text
);
DDL);

        $builder->batchInsert('class', ['id', 'school_id', 'name'], [
            ['1', '1', 'I-7'],
            ['2', '1', 'II-1'],
            ['3', '1', 'II-2'],
            ['4', '2', 'One'],
            ['5', '2', 'Two'],
        ]);
        $builder->batchInsert('migration', ['id', 'version', 'created_at'], []);
        $builder->batchInsert('school', ['id', 'name'], [
            ['1', '托尔兹士官学院'],
            ['2', '警察学院'],
        ]);
        $builder->batchInsert('story', ['id', 'title', 'desc', 'content'], [
            ['1', '第一章', '神狼的午后', '罗伊德在特别任务支援科总部见到了唐古拉门守备军的总指挥，克洛斯贝尔自治州警备队副司令索妮亚的委托，调查频频在首府周边发生的犬型魔兽袭人事件。'],
            ['2', '第二章', '金之太阳、银之月', '克洛斯贝尔市的彩虹剧团名扬四海，拥有当家花旦，人称炎之舞姬的伊莉娅·普拉提耶，一次偶然中发现了潜力巨大的女孩丽霞·毛并且邀请其加入彩虹剧团，和自己共同排练新作品《金之太阳·银之月》。'],
            ['3', '第三章', '克洛斯贝尔创立纪念庆典', '克洛斯贝尔自治州迎来5天的建州70周年祭，罗伊德一行人由于在恐吓信事件中的杰出表现而获得了一天的休假。4人各自行动。'],
            ['4', '第四章', '悄然袭来的睿智', '矿山镇玛因兹的镇长向特别任务支援科求助，说一名矿工冈兹失踪数日。'],
            ['5', '终章', '克洛斯贝尔最漫长的一日', '罗伊德等人欲再前往医科大学探寻蓝色药丸的成分，达德利告诉罗伊德，阿奈斯特在被捕后陷入了神智错乱，而约亚西姆一直是其主治医生，几人遂开始怀疑约亚西姆。'],
        ]);
        $builder->batchInsert('student', ['id', 'class_id', 'name', 'password', 'age', 'birthday', 'sex', 'desc'], [
            ['1', '1', 'Rean Schwarzer', '2JhpOUImZXwN0szn', '17', '2003-1-1', '1', ''],
            ['2', '1', 'Alisa Reinford', 'SsPA15Rnb9VQOveD', '16', '2004-2-2', '2', ''],
            ['3', '1', 'Fei Claussell', 'RzUukSAB3qifgDs1', '15', '2005-3-3', '2', ''],
            ['4', '2', 'Crow Armbrust', 'LOCh5XDvSstiqAPd', '19', '2001-4-4', '1', ''],
            ['5', '3', 'Angelica Rogner', 'xCNEuZ7APkX0jMnr', '20', '2000-5-5', '2', ''],
            ['6', '4', 'Lloyd Bannings', '2hrCNXYLeli45x6b', '18', '2002-5-21', '1', ''],
            ['7', '4', 'Randy Orlando', 'MnGelxHhj95YyJQq', '21', '1999-8-12', '1', ''],
            ['8', '5', 'Elie MacDowell', '5k2gZsudxR6JlmUA', '18', '2002-7-5', '2', ''],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('class');
        $builder->dropTable('school');
        $builder->dropTable('story');
        $builder->dropTable('student');
    }
}
