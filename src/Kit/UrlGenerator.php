<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\RouteCollection;
use FastRoute\RouteParser;
use LogicException;
use RuntimeException;

final class UrlGenerator
{
    private RouteParser $routeParser;

    public function __construct(
        private RouteCollection $routeCollection
    ) {
        $this->routeParser = new RouteParser\Std();
    }

    public function generate(string $name, array $arguments = []): string
    {
        $rule = $this->routeCollection->getNames()[$name] ?? null;
        if ($rule === null) {
            throw new LogicException(sprintf('The route name "%s" is not exists.', $name));
        }
        [, $pattern,] = $rule;

        $parsedRoutes = array_reverse($this->routeParser->parse($pattern));
        if (!$parsedRoutes) {
            throw new LogicException(sprintf('The route name "%s" is invalid.', $name));
        }

        foreach ($parsedRoutes as $routeParts) {
            $missingArguments = $this->getMissingArguments($routeParts, $arguments);
            if ($missingArguments) {
                continue;
            }

            return $this->generateParsedRoute($routeParts, $arguments);
        }

        throw new RuntimeException(
            sprintf(
                'The route name `%s` expects arguments [%s], but received [%s].',
                $name,
                implode(', ', $missingArguments),
                implode(', ', array_keys($arguments))
            )
        );
    }

    private function generateParsedRoute(array $routeParts, array $arguments): string
    {
        $result = '';
        $restArguments = $arguments;
        foreach ($routeParts as $part) {
            if (is_string($part)) {
                $result .= $part;
                continue;
            }

            if ($arguments[$part[0]]) {
                if (preg_match('~^' . str_replace('~', '\~', $part[1]) . '$~', $arguments[$part[0]]) === 0) {
                    throw new RuntimeException(sprintf('Argument [%s] did not match the regex `%s`', $part[0], $part[1]));
                }
                $result .= rawurlencode($arguments[$part[0]]);
            }

            unset($restArguments[$part[0]]);
        }

        return str_replace('//', '/', $result) . ($restArguments ? '?' . http_build_query($restArguments) : '');
    }

    private function getMissingArguments(array $routeParts, array $arguments): array
    {
        $missingArguments = [];
        foreach ($routeParts as $part) {
            if (is_string($part)) {
                continue;
            }
            $missingArguments[] = $part[0];
        }

        foreach ($missingArguments as $argument) {
            if (!array_key_exists($argument, $arguments)) {
                return $missingArguments;
            }
        }
        return [];
    }
}
