<?php

declare(strict_types=1);

namespace Ep\Base;

final class Constant
{
    public const ATTRIBUTE_TARGET = 'target';

    public const REQUEST_ATTRIBUTE_EXCEPTION = '__exception';
    public const REQUEST_ATTRIBUTE_CONTROLLER = '__controller-id';
    public const REQUEST_ATTRIBUTE_ACTION = '__action-id';

    public const CACHE_ATTRIBUTE_DATA = 'Ep-Cache-Attribute-Data';
}
