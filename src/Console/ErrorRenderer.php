<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ErrorRenderer as BaseErrorRenderer;
use Ep\Contract\ConsoleErrorRendererInterface;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorRenderer extends BaseErrorRenderer
{
    public function __construct(
        private ContainerInterface $container,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    public function render(Throwable $t, $request): string
    {
        if ($this->container->has(ConsoleErrorRendererInterface::class)) {
            return $this->container
                ->get(ConsoleErrorRendererInterface::class)
                ->render($t, $request);
        } else {
            $this->log($t, $request);

            return parent::render($t, $request);
        }
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    private function log(Throwable $t, $request): void
    {
        $context = [
            'category' => get_class($t)
        ];

        $context['route'] = $request->getRoute();
        $context['arguments'] = $request->getArguments();
        $context['options'] = $request->getOptions();

        $this->logger->error(parent::render($t, $request), $context);
    }
}
