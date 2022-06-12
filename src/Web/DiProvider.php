<?php

declare(strict_types=1);

namespace Ep\Web;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

final class DiProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Application
            Application::class => [
                '__construct()' => [
                    'fallbackHandler' => Reference::to(NotFoundHandler::class)
                ]
            ],
            // Request
            ServerRequestFactoryInterface::class => ServerRequestFactory::class,
            UriFactoryInterface::class => UriFactory::class,
            UploadedFileFactoryInterface::class => UploadedFileFactory::class,
            StreamFactoryInterface::class => StreamFactory::class,
            // Response
            ResponseFactoryInterface::class => ResponseFactory::class,
            // Session
            SessionInterface::class => [
                'class' => Session::class,
                '__construct()' => [
                    ['cookie_secure' => 0]
                ]
            ],
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
