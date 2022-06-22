<?php

declare(strict_types=1);

namespace Ep\Base;

final class Constant
{
    public const ATTRIBUTE_TARGET = 'target';

    public const METHOD_AROUND = '__around';
    public const METHOD_MIDDLEWARE_GET = '__getMiddlewares';

    public const REQUEST_CONTROLLER = '__controller-id';
    public const REQUEST_ACTION = '__action-id';

    public const CACHE_ATTRIBUTE_DATA = 'Ep-Cache-Attribute-Data';
}
