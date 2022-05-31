<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Contract\ContextTrait;
use Ep\Contract\ContextInterface;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandler implements RequestHandlerInterface, ContextInterface
{
    use ContextTrait;

    /**
     * {@inheritDoc}
     */
    public string $id = 'error';

    public function __construct(private Service $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->string(
            $this->getView()->renderPartial('notFound', [
                'path' => $request->getUri()->getPath(),
                'exception' => $request->getAttribute('exception')
            ]),
            Status::NOT_FOUND
        );
    }

    protected function getViewPath(): string
    {
        return '@ep/views';
    }
}
