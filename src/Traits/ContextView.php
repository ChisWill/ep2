<?php

declare(strict_types=1);

namespace Ep\Traits;

use Ep;
use Ep\Base\View;

trait ContextView
{
    private ?View $__view = null;

    protected function getView(): View
    {
        if ($this->__view === null) {
            $this->__view = $this->createView();
        }
        return $this->__view;
    }

    protected function getViewClass(): string
    {
        return View::class;
    }

    protected function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }

    protected function getContextId(): ?string
    {
        return null;
    }

    private function createView(): View
    {
        return Ep::getInjector()
            ->make($this->getViewClass())
            ->withViewPath($this->getViewPath())
            ->withContext($this)
            ->withContextId($this->getContextId());
    }
}
