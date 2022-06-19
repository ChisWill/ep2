<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Attribute\Inject;
use Ep\Console\Contract\ErrorRendererInterface;
use Throwable;
use Ep\Console\Contract\RequestInterface;
use Psr\Log\LoggerInterface;

class ConsoleRenderer implements ErrorRendererInterface
{
    #[Inject]
    private LoggerInterface $log;

    public function render(Throwable $t, RequestInterface $request): string
    {
        $this->log($t, $request);

        return $this->renderContent($t, $request);
    }

    private function renderContent(Throwable $t, RequestInterface $request): string
    {
        return sprintf(
            "%s: %s, File: %s\n",
            get_class($t),
            $t->getMessage(),
            $t->getFile() . ':' . $t->getLine()
        );
    }

    private function log(Throwable $t, RequestInterface $request): void
    {
        $context = [
            'category' => get_class($t),
            'route' => $request->getRoute(),
            'arguments' => $request->getArguments(),
            'options' => $request->getOptions()
        ];

        $this->log->emergency($this->renderContent($t, $request), $context);
    }
}
