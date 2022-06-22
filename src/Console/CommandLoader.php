<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Config;
use Ep\Base\RouteParser;
use Ep\Base\Router;
use Ep\Console\Contract\FactoryInterface;
use Ep\Console\Trait\Renderer;
use Ep\Exception\PageNotFoundException;
use Ep\Helper\Str;
use Ep\Kit\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

final class CommandLoader implements CommandLoaderInterface
{
    public function __construct(
        private Config $config,
        private Router $router,
        private RouteParser $parser,
        private Runner $runner,
        private FactoryInterface $factory,
        private Util $util
    ) {
        $this->router = $router
            ->withSuffix($config->commandSuffix)
            ->withEnableAttributeRule(false)
            ->withEnableCollectioneRule(false);
        $this->parser = $parser->withSuffix($config->commandSuffix);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): Command
    {
        return $this->wrapCommand($name);
    }

    private function wrapCommand(string $name): Command
    {
        $commandName = $this->parse($name);

        [$controller, $action] = $this->parser->parse($commandName);
        return new class($controller, $action, $this->runner, $this->factory, $commandName, $name) extends Command
        {
            public function __construct(
                private object $controller,
                private string $action,
                private Runner $runner,
                private FactoryInterface $factory,
                string $name,
                string $alias
            ) {
                if ($name !== $alias) {
                    $this->setAliases([$alias]);
                }

                parent::__construct($name);
            }

            /**
             * {@inheritdoc}
             */
            protected function configure(): void
            {
                $definitions = isset(class_uses($this->controller)[Renderer::class]) ? $this->controller->__getDefinitions() : [];
                if (isset($definitions[$this->action])) {
                    $this
                        ->setDefinition($definitions[$this->action]->getDefinitions())
                        ->setDescription($definitions[$this->action]->getDescription())
                        ->setHelp($definitions[$this->action]->getHelp());
                    foreach ($definitions[$this->action]->getUsages() as $usage) {
                        $this->addUsage($usage);
                    }
                }
            }

            /**
             * {@inheritdoc}
             */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return $this->runner
                    ->runAction(
                        $this->controller,
                        $this->action,
                        $this->factory->createRequest($input),
                        $this->factory->createResponse($output)
                    )
                    ->getCode();
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return in_array($this->parse($name), $this->getNames());
    }

    private array $commandNames = [];

    /**
     * @throws InvalidArgumentException
     * @throws PageNotFoundException
     */
    private function parse(string $name): string
    {
        if (!isset($this->commandNames[$name])) {
            [, $handler] = $this->router->match('/' . $name);
            [$class, $actionId] = $this->parser->parseHandler($handler);

            $this->commandNames[$name] = $this->getCommandName(
                preg_replace('~' . str_replace('\\', '/', $this->config->rootNamespace) . '/~', '', str_replace('\\', '/', $class), 1),
                Str::camelToId($actionId, '-'),
            );
        }
        return $this->commandNames[$name];
    }

    private ?array $commands = null;

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        if ($this->commands === null) {
            $this->commands = $this->getCommands($this->getCommandFiles());
        }
        return $this->commands;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getCommandFiles(): array
    {
        return $this->getFiles(str_replace('\\', '/', $this->util->getAppPath()), $this->config->commandSuffix);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getFiles(string $directory, string $suffix): array
    {
        return array_map(static function ($filePath) use ($directory): string {
            return trim(str_replace([$directory, '.php'], '', $filePath), '/');
        }, FileHelper::findFiles($directory, [
            'filter' => (new PathMatcher())->only('**' . $suffix . '/*' . $suffix . '.php')
        ]));
    }

    private function getCommands(array $files): array
    {
        $map = [];
        foreach ($files as $className) {
            $map[$className] = array_filter(
                (new ReflectionClass($this->config->rootNamespace . '\\' . str_replace('/', '\\', $className)))->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $ref): bool => !str_starts_with($ref->getName(), '__')
            );
        }
        $commands = [];
        foreach ($map as $className => $actions) {
            foreach ($actions as $ref) {
                $commands[] = $this->getCommandName(
                    $className,
                    Str::camelToId($ref->getName(), '-', true)
                );
            }
        }
        return $commands;
    }

    private function getCommandName(string $className, string $action): string
    {
        if ($action === $this->config->defaultAction && substr_count($className, '/') === 1) {
            $action = '';
        } else {
            $action = '/' . $action;
        }
        $prefix = trim(Str::camelToId(Str::rtrim('/' . $className, '/' . $this->config->commandSuffix, false), '-', true), '/');
        $basename = Str::camelToId(basename($className, $this->config->commandSuffix), '-', true);
        if ($prefix) {
            return sprintf('%s/%s%s', $prefix, $basename, $action);
        } else {
            return $basename . $action;
        }
    }
}
