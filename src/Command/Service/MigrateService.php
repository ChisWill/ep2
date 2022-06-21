<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Base\Config;
use Ep\Base\Contract\MigrateInterface;
use Ep\Command\Helper\MigrateBuilder;
use Ep\Console\Service as ConsoleService;
use Ep\Db\ActiveRecord;
use Ep\Db\Query;
use Ep\Db\Service as DbService;
use Ep\Helper\Date;
use Ep\Helper\File;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Closure;
use RuntimeException;
use Throwable;

final class MigrateService extends Service
{
    public function __construct(
        private Config $config,
        private Aliases $aliases,
        private ConsoleService $consoleService,
        private GenerateService $generateService
    ) {
    }

    private string $app;
    private string $tableName;
    private string $basePath;
    private MigrateBuilder $builder;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->app = $this->getRequest()->getOption('app') ?? 'default';
        $this->tableName = $this->defaultOptions['table'] ?? 'migration';
        $this->basePath = $this->aliases->get($this->defaultOptions['path'] ?? '@root/migrations');
        $this->builder = new MigrateBuilder($this->getDb(), $this->consoleService);
    }

    public function create(string $name): void
    {
        $this->createFile($this->generateClassName($name), '/M' . date('Ym'), 'migrate/default', compact('name'));
    }

    /**
     * @throws RuntimeException
     */
    public function init(): void
    {
        $this->createTable();

        $dbService = new DbService($this->getDb());

        $name = 'Initialization';
        $upSql = $downSql = '';
        $tables = $dbService->getTables($this->getRequest()->getOption('prefix') ?? '');
        foreach ($tables as $tableName) {
            if ($tableName !== $this->tableName) {
                $upSql .= $dbService->getDDL($tableName) . ";\n";
                $downSql .= sprintf('%s$builder->dropTable(\'%s\');%s', str_repeat(' ', 8), $tableName, "\n");
            }
        }
        $insertData = [];
        if ($this->getRequest()->getOption('data')) {
            foreach ($tables as $tableName) {
                $data = Query::find($this->getDb())->from($tableName)->all();
                if (!$data) {
                    continue;
                }
                $insertData[$tableName] = [
                    'columns' => array_keys($data[0]),
                    'rows' => $data
                ];
            }
        }

        $this->createFile($name, '', 'migrate/init', compact('name', 'upSql', 'downSql', 'insertData'));
    }

    public function list(): void
    {
        $this->createTable();

        $list = array_map(fn ($path): string => $this->getClassNameByPath($path), $this->findMigrations());
        sort($list);

        $history = $this->getHistory();

        $total = count($list);
        $this->consoleService->writeln(sprintf('Total <info>%d</> migration%s found in <comment>%s</>', $total, $total > 1 ? 's' : '', $this->basePath));
        foreach ($list as $class) {
            if (in_array($class, $history)) {
                $status = 'executed';
                $color = 'yellow';
            } else {
                $status = 'pending';
                $color = 'magenta';
            }
            /** @var MigrateInterface $class */
            $this->consoleService->writeln(sprintf('- <info>%s</> [<fg=%s>%s</>]', $class::getName(), $color, $status));
        }
    }

    private int $step;

    public function up(): void
    {
        $this->step = (int) ($this->getRequest()->getOption('step') ?? 0);

        $this->migrate('up', function (array $instances): bool {
            if (!$instances) {
                $this->consoleService->writeln('Already up to date.');
                return false;
            }
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be applied:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Apply the above migration%s?', $count > 1 ? 's' : ''), true);
        }, function (array $instances): void {
            $this->builder->batchInsert(
                $this->tableName,
                ['app', 'version', ActiveRecord::CREATED_AT],
                array_map(fn ($instance): array => [$this->app, get_class($instance), Date::fromUnix()], $instances)
            );

            $this->consoleService->writeln(sprintf('Commit count: %d.', count($instances)));
        });
    }

    public function down(): void
    {
        $this->step = $this->getRequest()->getOption('all') ? 0 : (int) ($this->getRequest()->getOption('step') ?? 1);

        $this->migrate('down', function (array $instances): bool {
            if (!$instances) {
                $this->consoleService->writeln('No commits.');
                return false;
            }
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be reverted:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Revert the above migration%s?', $count > 1 ? 's' : ''));
        }, function (array $instances): void {
            $this->builder->delete(
                $this->tableName,
                [
                    'app' => $this->app,
                    'version' => array_map(fn ($instance): string => get_class($instance), $instances)
                ]
            );

            $this->consoleService->writeln(sprintf('Revert count: %d.', count($instances)));
        });
    }

    private function migrate(string $method, Closure $before, Closure $after): void
    {
        $this->createTable();

        $files = $this->findMigrations();

        $history = $this->getHistory();

        switch ($method) {
            case 'up':
                sort($files);
                $reverse = false;
                break;
            case 'down':
                rsort($files);
                $reverse = true;
                break;
            default:
                $this->error('Unsupport migrate method.');
        }

        $instances = [];
        $count = 0;
        foreach ($files as $file) {
            if ($this->step > 0 && $count === $this->step) {
                break;
            }
            $className = $this->getClassNameByPath($file);
            if (!class_exists($className)) {
                $this->error("The class {$className} is not exists.");
            }
            $skip = in_array($className, $history);
            if ($reverse) {
                $skip = !$skip;
            }
            if ($skip) {
                continue;
            }
            $instance = new $className();
            if (!$instance instanceof MigrateInterface) {
                $this->error(sprintf('The class %s is not implements %s.', $className, MigrateInterface::class));
            }
            $instances[] = $instance;
            $count++;
        }

        if (call_user_func($before, $instances)) {
            $transaction = $this->getDb()->beginTransaction();
            try {
                foreach ($instances as $instance) {
                    call_user_func([$instance, $method], $this->builder);
                }
            } catch (Throwable $t) {
                $transaction->rollBack();
                $this->error(sprintf("%s::%s() failed, because %s.", $className, $method, $t->getMessage()));
            }

            try {
                call_user_func($after, $instances);
                if ($this->getDb()->getPDO()->inTransaction()) {
                    $transaction->commit();
                }
            } catch (Throwable $t) {
                $transaction->rollBack();
                $this->error($t->getMessage());
            }
        }
    }

    private function getHistory(): array
    {
        return Query::find($this->getDb())
            ->select('version')
            ->from($this->tableName)
            ->where([
                'app' => $this->app
            ])
            ->column();
    }

    /**
     * @throws RuntimeException
     */
    private function findMigrations(): array
    {
        $this->createDir();

        return FileHelper::findFiles($this->basePath, [
            'filter' => (new PathMatcher())->only('**.php')
        ]);
    }

    /**
     * @throws RuntimeException
     */
    private function createFile(string $className, string $classPath, string $view, array $params = []): bool
    {
        $this->createDir($classPath);

        $params['className'] = $className;
        if (@file_put_contents(sprintf('%s/%s.php', $this->basePath . $classPath, $className), $this->generateService->render($view, $params))) {
            $this->consoleService->writeln(sprintf('New migration file <info>%s.php</> has been created in <comment>%s</>', $className, $this->basePath . $classPath));
            return true;
        } else {
            $this->consoleService->writeln('<error>Generate failed.</>');
            return false;
        }
    }

    private function generateClassName(string $name): string
    {
        $base = sprintf('M%s_', date('Ymd_Hi'));

        $files = FileHelper::findFiles($this->basePath, [
            'filter' => (new PathMatcher())->only("**{$base}*.php")
        ]);

        return sprintf('%s%d_%s', $base, count($files) + 1, $name);
    }

    /**
     * @throws RuntimeException
     */
    private function createDir(string $dir = ''): void
    {
        $basePath = $dir ? $this->basePath . '/' . $dir : $this->basePath;
        if (!file_exists($basePath)) {
            File::mkdir($basePath);
        }
    }

    private function getClassNameByPath(string $path): string
    {
        $class = basename($path, '.php');
        if (!class_exists($class)) {
            require($path);
        }
        return $class;
    }

    private function createTable(): void
    {
        try {
            $this->builder->find()->from($this->tableName)->one();
        } catch (Exception $t) {
            $this->builder->createTable($this->tableName, [
                'id' => $this->builder->primaryKey(),
                'app' => $this->builder->string(50)->notNull(),
                'version' => $this->builder->string(100)->notNull(),
                ActiveRecord::CREATED_AT => $this->builder->dateTime()->notNull()
            ]);
            $this->builder->createIndex('idx_version', $this->tableName, 'version');
        }
    }
}
