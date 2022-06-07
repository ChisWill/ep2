<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Constant;
use Ep\Traits\ViewTrait;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandler implements RequestHandlerInterface
{
    use ViewTrait;

    public function __construct(private Service $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->string(
            $this->getView()->renderPartial('notFound', [
                'path' => $request->getUri()->getPath(),
                'exception' => $request->getAttribute(Constant::REQUEST_ATTRIBUTE_EXCEPTION)
            ]),
            Status::NOT_FOUND
        );
    }

    protected function getViewPath(): string
    {
        return '@ep/views';
    }

    protected function getContextId(): string
    {
        return 'error';
    }
}
