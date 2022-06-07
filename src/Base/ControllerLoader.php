<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\ControllerLoaderResult;
use Ep\Contract\ControllerInterface;
use Ep\Exception\NotFoundException;
use Ep\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

final class ControllerLoader
{
    private string $suffix;

    public function __construct(
        private ContainerInterface $container,
        private Config $config
    ) {
        $this->suffix = PHP_SAPI === 'cli' ? $config->commandSuffix : $config->controllerSuffix;
    }

    public function withSuffix(string $suffix): self
    {
        $new = clone $this;
        $new->suffix = $suffix;
        return $new;
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function parse(string|array $handler): ControllerLoaderResult
    {
        [$class, $actionId] = $this->parseHandler($handler);

        return new ControllerLoaderResult(
            $this->createController($class, $actionId),
            $this->createAction($actionId)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function parseHandler(string|array $handler): array
    {
        switch (gettype($handler)) {
            case 'string':
                return $this->parseStringHandler($handler);
            case 'array':
                return $this->parseArrayHandler($handler);
        }
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function createController(string $class, string $actionId): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new NotFoundException("{$class} is not found.");
        }

        return $this->container
            ->get($class)
            ->configure([
                'id' => $this->generateContextId($class),
                'actionId' => $actionId
            ]);
    }

    private function createAction(string $actionId): string
    {
        return $actionId . $this->config->actionSuffix;
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
                $prefix ? (strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->suffix : str_replace('\\\\', '\\' . $this->suffix . '\\', $prefix)) : $this->suffix,
                Str::toPascalCase($controller) . $this->suffix
            ),
            lcfirst(Str::toPascalCase($action))
        ];
    }

    private function generateContextId(string $class): string
    {
        return implode('/', array_filter(
            array_map('lcfirst', explode(
                '\\',
                str_replace([$this->config->rootNamespace, $this->suffix], '', $class)
            ))
        ));
    }
}
