<?php

declare(strict_types=1);

namespace Ep\Web\Trait;

use Ep\Attribute\Inject;
use Ep\Base\Config;
use Ep\Base\Trait\ContextView;
use Ep\Kit\Util;
use Ep\Web\Service;
use Ep\Web\View;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplFileInfo;

trait WebService
{
    use ContextView;

    #[Inject]
    private Service $__service;
    #[Inject]
    private Config $__config;
    #[Inject]
    private Util $__util;

    protected function string(mixed $data = '', int $statusCode = Status::OK): ResponseInterface
    {
        return $this->__service->string((string) $data, $statusCode);
    }

    protected function json(mixed $data = []): ResponseInterface
    {
        return $this->__service->json($data);
    }

    protected function status(int $statusCode = Status::OK): ResponseInterface
    {
        return $this->__service->status($statusCode);
    }

    protected function render(string $view, array $params = [], string $layout = null): ResponseInterface
    {
        return $this->__service->string(
            $this->getView()->withLayout($layout)->render($view, $params)
        );
    }

    protected function renderPartial(string $view, array $params = []): ResponseInterface
    {
        return $this->__service->string(
            $this->getView()->renderPartial($view, $params)
        );
    }

    protected function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->__service->redirect($url, $statusCode);
    }

    protected function download(ServerRequestInterface $request, SplFileInfo|string $file, string $name = null): ResponseInterface
    {
        return $this->__service->download($request, $file, $name);
    }

    protected function getViewClass(): string
    {
        return View::class;
    }

    protected function getContextId(): ?string
    {
        return $this->__util->generateClassId(static::class, $this->__config->controllerSuffix);
    }
}
