<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Contract\ErrorRendererInterface;
use Ep\Console\Contract\ErrorRendererInterface as ConsoleErrorRendererInterface;
use Ep\Console\Contract\RequestInterface;
use Ep\Kit\ErrorMessage;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private ContainerInterface $container,
        private LoggerInterface $logger,
        private ErrorMessage $errorMessage
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function render(Throwable $t, mixed $request): string
    {
        if ($this->container->has(ConsoleErrorRendererInterface::class)) {
            return $this->container
                ->get(ConsoleErrorRendererInterface::class)
                ->render($t, $request);
        } else {
            $this->log($t, $request);

            return $this->errorMessage->getMessage($t);
        }
    }

    private function log(Throwable $t, RequestInterface $request): void
    {
        $this->logger->error($this->errorMessage->getMessage($t), [
            'category' => get_class($t),
            'route' => $request->getRoute(),
            'arguments' => $request->getArguments(),
            'options' => $request->getOptions()
        ]);
    }
}
