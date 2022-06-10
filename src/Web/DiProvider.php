<?php

declare(strict_types=1);

namespace Ep\Web;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Yiisoft\Di\ServiceProviderInterface;

final class DiProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // ServerRequest
            ServerRequestFactoryInterface::class => ServerRequestFactory::class,
            UriFactoryInterface::class => UriFactory::class,
            UploadedFileFactoryInterface::class => UploadedFileFactory::class,
            StreamFactoryInterface::class => StreamFactory::class,
            // Response
            ResponseFactoryInterface::class => ResponseFactory::class
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
