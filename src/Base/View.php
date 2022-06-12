<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Attribute\Inject;
use Yiisoft\Aliases\Aliases;

class View
{
    #[Inject]
    private Config $config;
    #[Inject]
    private Aliases $aliases;

    private string $layout = 'main';

    public function withLayout(?string $layout): self
    {
        if ($layout === null) {
            return $this;
        }
        $new = clone $this;
        $new->layout = $layout;
        return $new;
    }

    private ?string $viewPath = null;

    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    private ?object $context = null;

    public function withContext(object $context): self
    {
        $new = clone $this;
        $new->context = $context;
        return $new;
    }

    private ?string $contextId = null;

    public function withContextId(?string $contextId): self
    {
        if ($contextId === null) {
            return $this;
        }
        $new = clone $this;
        $new->contextId = $contextId;
        return $new;
    }

    public function render(string $path, array $params = []): string
    {
        return $this->renderLayout($this->layout, [
            'content' => $this->renderPartial($path, $params)
        ]);
    }

    public function renderPartial(string $path, array $params = []): string
    {
        return $this->renderPHPFile($this->findFilePath($this->normalize($path)), $params);
    }

    public function renderFile(string $file): string
    {
        return file_get_contents($this->findFilePath($this->normalize($file), ''));
    }

    private function normalize(string $path): string
    {
        if ($this->contextId !== null && !str_starts_with($path, '/')) {
            $path = sprintf('/%s/%s', $this->contextId, $path);
        }
        return $path;
    }

    private function getViewPath(): string
    {
        if ($this->viewPath === null) {
            $this->viewPath = $this->config->viewPath;
        }
        return $this->viewPath;
    }

    private function renderLayout(string $layout, array $params = []): string
    {
        if (!str_starts_with($layout, '/')) {
            if ($this->contextId === null || ($pos = strrpos($this->contextId, '/')) === false) {
                $layout = sprintf('/%s/%s', $this->config->layoutDir, $layout);
            } else {
                $layout = sprintf('/%s/%s/%s', substr($this->contextId, 0, $pos), $this->config->layoutDir, $layout);
            }
        }
        return $this->renderPHPFile($this->findFilePath($layout), $params);
    }

    private function findFilePath(string $view, string $ext = '.php'): string
    {
        return $this->aliases->get($this->getViewPath() . '/' . ltrim($view, '/') . $ext);
    }

    private function renderPHPFile(): string
    {
        ob_start();
        ob_implicit_flush(false);
        extract(func_get_arg(1), EXTR_OVERWRITE);
        require(func_get_arg(0));

        return ob_get_clean();
    }
}
