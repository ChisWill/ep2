<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Exception\PageNotFoundException;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandler implements RequestHandlerInterface
{
    public function __construct(
        private Service $service,
        private View $view
    ) {
        $this->view = $view
            ->withViewPath('@ep/views')
            ->withContext($this)
            ->withContextId('error');
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->string(
            $this->view->renderPartial('notFound', [
                'path' => $request->getUri()->getPath(),
                'exception' => $request->getAttribute(PageNotFoundException::class)
            ]),
            Status::NOT_FOUND
        );
    }
}
