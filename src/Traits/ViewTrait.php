<?php

declare(strict_types=1);

namespace Ep\Traits;

use Ep;
use Ep\Base\View;

trait ViewTrait
{
    private ?View $view = null;

    public function getView(): View
    {
        if ($this->view === null) {
            $this->view = $this->createView();
        }
        return $this->view;
    }

    private function createView(): View
    {
        return  Ep::getInjector()
            ->make($this->getViewClass())
            ->withViewPath($this->getViewPath())
            ->withContext($this)
            ->withContextId($this->getContextId());
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
}
