<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep;
use Ep\Base\Config;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Composer\Autoload\ClassLoader;
use InvalidArgumentException;

final class Util
{
    public function __construct(
        private Config $config,
        private Aliases $aliases
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function rootPath(string $path = ''): string
    {
        return $this->aliases->get('@root') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function vendorPath(string $path = ''): string
    {
        return $this->aliases->get('@vendor') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getClassList(string $rootNamespace, array $exceptPatterns = []): array
    {
        $result = [];
        foreach ($this->findClassFiles($this->getAppPath($rootNamespace), $exceptPatterns) as $file) {
            $result[] = $this->getClassNameByFile($rootNamespace, $file);
        }
        return $result;
    }

    private array $appPath = [];

    /**
     * @throws InvalidArgumentException
     */
    public function getAppPath(string $rootNamespace = null): string
    {
        $rootNamespace ??= $this->config->rootNamespace;
        if (!isset($this->appPath[$rootNamespace])) {
            if (Ep::isSelf($rootNamespace)) {
                $this->appPath[$rootNamespace] = $this->aliases->get('@ep/src');
            } else {
                $this->appPath[$rootNamespace] = $this->getAppPathByComposer($rootNamespace);
            }
        }
        return $this->appPath[$rootNamespace];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getAppPathByComposer(string $rootNamespace): string
    {
        $rootNamespace = trim($rootNamespace, '\\') . '\\';
        /** @var ClassLoader */
        $classLoader = require($this->vendorPath('autoload.php'));
        foreach ($classLoader->getPrefixesPsr4() as $prefix => $paths) {
            if (strpos($rootNamespace, $prefix) === 0) {
                $path = rtrim(str_replace('\\', '/',  realpath(current($paths)) . str_replace($prefix, DIRECTORY_SEPARATOR, $rootNamespace)), '/');
                break;
            }
        }
        if (!isset($path)) {
            throw new InvalidArgumentException('You should set the "autoload[psr-4]" configuration in your composer.json first.');
        }
        return $path;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getClassNameByFile(string $rootNamespace, string $file): string
    {
        return str_replace([$this->getAppPath($rootNamespace), '.php', '/'], [$rootNamespace, '', '\\'], $file);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findClassFiles(string $path, array $exceptPatterns = []): array
    {
        return FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())->only('**.php')->except(...$exceptPatterns)
        ]);
    }
}
