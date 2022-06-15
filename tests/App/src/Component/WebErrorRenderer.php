<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Attribute\Inject;
use Ep\Base\Trait\ContextView;
use Ep\Web\Contract\WebErrorRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class WebErrorRenderer implements WebErrorRendererInterface
{
    use ContextView;

    #[Inject]
    private LoggerInterface $log;

    public function render(Throwable $t, ServerRequestInterface $request): string
    {
        $this->log($t, $request);

        return $this->getView()->renderPartial('error', compact('t', 'request'));
    }

    private function log(Throwable $t, ServerRequestInterface $request): void
    {
        $this->log->critical($t->getMessage());
    }

    protected function getContextId(): ?string
    {
        return 'demo';
    }
}
