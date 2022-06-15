<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ErrorHandler;
use Ep\Base\Contract\ApplicationInterface;
use Ep\Web\Middleware\InterceptorMiddleware;
use Ep\Web\Middleware\RouteMiddleware;
use HttpSoft\Emitter\Exception\HeadersAlreadySentException;
use HttpSoft\Emitter\Exception\OutputAlreadySentException;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Application implements ApplicationInterface
{
    public function __construct(
        private ServerRequestCreator $serverRequestCreator,
        private RequestHandlerFactory $requestHandlerFactory,
        private SapiEmitter $sapiEmitter,
        private ErrorRenderer $errorRenderer,
        private RequestHandlerInterface $fallbackHandler
    ) {
    }

    private array $middlewares = [
        InterceptorMiddleware::class,
        SessionMiddleware::class,
        RouteMiddleware::class
    ];

    public function withMiddlewares(array $middlewares): self
    {
        $new = clone $this;
        $new->middlewares = $middlewares;
        return $new;
    }

    /**
     * @throws HeadersAlreadySentException
     * @throws OutputAlreadySentException
     */
    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->emit($request, $this->handleRequest($request));
    }

    public function createRequest(): ServerRequestInterface
    {
        return $this->serverRequestCreator->createFromGlobals();
    }

    public function register(ServerRequestInterface $request): void
    {
        ErrorHandler::create($this->errorRenderer)->register($request);
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->requestHandlerFactory
            ->wrap($this->middlewares, $this->fallbackHandler)
            ->handle($request);
    }

    /**
     * @throws HeadersAlreadySentException
     * @throws OutputAlreadySentException
     */
    public function emit(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->sapiEmitter->emit(
            $response,
            $request->getMethod() === Method::HEAD
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getDiProviderName(): ?string
    {
        return DiProvider::class;
    }
}
