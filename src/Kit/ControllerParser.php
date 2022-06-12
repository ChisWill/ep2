<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use Ep\Exception\PageNotFoundException;
use Ep\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

final class ControllerParser
{
    private string $suffix;

    public function __construct(
        private ContainerInterface $container,
        private Config $config
    ) {
        $this->suffix = $config->controllerSuffix;
    }

    public function withSuffix(string $suffix): self
    {
        $new = clone $this;
        $new->suffix = $suffix;
        return $new;
    }

    /**
     * @throws InvalidArgumentException
     * @throws PageNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function parse(string|array $handler): array
    {
        [$class, $action] = $this->parseHandler($handler);

        return [
            $this->createController($class),
            $action
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function parseHandler(string|array $handler): array
    {
        if (is_string($handler)) {
            return $this->parseStringHandler($handler);
        } else {
            return $this->parseArrayHandler($handler);
        }
    }

    /**
     * @throws PageNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function createController(string $class): object
    {
        if (!class_exists($class)) {
            throw new PageNotFoundException("{$class} is not found.");
        }

        return $this->container->get($class);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function parseArrayHandler(array $handler): array
    {
        switch (count($handler)) {
            case 1:
                array_push($handler, $this->config->defaultAction);
            case 2:
                $suffixPos = strpos($handler[0], '\\' . $this->suffix . '\\');
                if ($suffixPos === false) {
                    throw new InvalidArgumentException('The route handler is not in the correct directory.');
                }
                array_unshift($handler, str_replace($this->config->rootNamespace . '\\', '', substr($handler[0], 0, $suffixPos)));
                break;
            default:
                throw new InvalidArgumentException('The route handler is not in the correct format.');
        }
        return $handler;
    }

    private function parseStringHandler(string $handler): array
    {
        $pieces = explode('/', $handler);
        $prefix = '';
        switch (count($pieces)) {
            case 0:
                $controller = $this->config->defaultController;
                $action = $this->config->defaultAction;
                break;
            case 1:
                $controller = $pieces[0];
                $action = $this->config->defaultAction;
                break;
            default:
                $action = array_pop($pieces) ?: $this->config->defaultAction;
                $controller = array_pop($pieces) ?: $this->config->defaultController;
                $prefix = implode('\\', array_map([Str::class, 'toPascalCase'], $pieces));
                break;
        }

        return [
            sprintf(
                '%s\\%s\\%s',
                $this->config->rootNamespace,
                $prefix ? (str_contains($prefix, '\\\\') ? str_replace('\\\\', '\\' . $this->suffix . '\\', $prefix) : ($prefix . '\\' . $this->suffix)) : $this->suffix,
                Str::toPascalCase($controller) . $this->suffix
            ),
            lcfirst(Str::toPascalCase($action))
        ];
    }
}
