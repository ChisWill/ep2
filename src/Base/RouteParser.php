<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Exception\PageNotFoundException;
use Ep\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

final class RouteParser
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
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws PageNotFoundException
     */
    public function parse(string|array $handler): array
    {
        [$class, $action] = $this->parseHandler($handler);

        $callback = [$this->createController($class), $action];
        if (!is_callable($callback)) {
            throw new PageNotFoundException(sprintf('%s::%s() is not exists.', $class, $action));
        }

        return $callback;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PageNotFoundException
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
                return $handler;
            default:
                throw new InvalidArgumentException('The route handler is not in the correct format.');
        }
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
