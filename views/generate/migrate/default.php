<?= "<?php\n" ?>

declare(strict_types=1);

use Ep\Command\Helper\MigrateBuilder;
use Ep\Base\Contract\MigrateInterface;

final class <?= $className ?> implements MigrateInterface
{
    public static function getName(): string
    {
        return '<?= $name ?>';
    }

    public function up(MigrateBuilder $builder): void
    {
    }

    public function down(MigrateBuilder $builder): void
    {
    }
}
