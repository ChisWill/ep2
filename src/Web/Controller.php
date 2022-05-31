<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ContextTrait;
use Ep\Contract\ControllerInterface;
use Ep\Contract\FilterTrait;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

abstract class Controller implements ControllerInterface
{
    use ContextTrait, FilterTrait, ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public string $id;
    /**
     * {@inheritDoc}
     */
    public string $actionId;

    public function before(ServerRequestInterface $request): bool|ResponseInterface
    {
        return true;
    }

    public function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    private array $views = [];

    public function getView(): View
    {
        $this->views[$this->actionId] ??= $this->createView();

        return $this->views[$this->actionId];
    }

    private ?Service $service = null;

    protected function getService(): Service
    {
        if ($this->service === null) {
            $this->service = Ep::getDi()->get(Service::class);
        }
        return $this->service;
    }

    protected function string(mixed $data = '', int $statusCode = Status::OK): ResponseInterface
    {
        return $this->getService()->string((string) $data, $statusCode);
    }

    protected function json(mixed $data = []): ResponseInterface
    {
        return $this->getService()->json($data);
    }

    protected function status(int $statusCode = Status::OK): ResponseInterface
    {
        return $this->getService()->status($statusCode);
    }

    protected function render(string $view, array $params = []): ResponseInterface
    {
        return $this->getService()->string($this->getView()->render($view, $params));
    }

    protected function renderPartial(string $view, array $params = []): ResponseInterface
    {
        return $this->getService()->string($this->getView()->renderPartial($view, $params));
    }

    protected function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->getService()->redirect($url, $statusCode);
    }

    protected function download(SplFileInfo|string $file, string $name = null): ResponseInterface
    {
        return $this->getService()->download($file, $name);
    }

    protected function getViewClass(): string
    {
        return View::class;
    }
}