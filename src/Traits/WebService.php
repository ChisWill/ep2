<?php

declare(strict_types=1);

namespace Ep\Traits;

use Ep\Attribute\Inject;
use Ep\Contract\ContextTrait;
use Ep\Web\Service;
use Ep\Web\View;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplFileInfo;

trait WebService
{
    use ContextTrait;

    #[Inject]
    private Service $service;

    public string $id;

    public string $actionId;

    private array $views = [];

    public function getView(): View
    {
        $this->views[$this->actionId] ??= $this->createView();

        return $this->views[$this->actionId];
    }

    private function string(mixed $data = '', int $statusCode = Status::OK): ResponseInterface
    {
        return $this->service->string((string) $data, $statusCode);
    }

    private function json(mixed $data = []): ResponseInterface
    {
        return $this->service->json($data);
    }

    private function status(int $statusCode = Status::OK): ResponseInterface
    {
        return $this->service->status($statusCode);
    }

    private function render(string $view, array $params = []): ResponseInterface
    {
        return $this->service->string($this->getView()->render($view, $params));
    }

    private function renderPartial(string $view, array $params = []): ResponseInterface
    {
        return $this->service->string($this->getView()->renderPartial($view, $params));
    }

    private function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->service->redirect($url, $statusCode);
    }

    private function download(
        ServerRequestInterface $request,
        SplFileInfo|string $file,
        string $name = null
    ): ResponseInterface {
        return $this->service
            ->withRequest($request)
            ->download($file, $name);
    }

    private function getViewClass(): string
    {
        return View::class;
    }
}
