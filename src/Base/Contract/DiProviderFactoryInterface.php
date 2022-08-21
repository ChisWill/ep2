<?php

declare(strict_types=1);

namespace Ep\Base\Contract;

use Yiisoft\Di\ServiceProviderInterface;

interface DiProviderFactoryInterface
{
    public static function createDiProvider(): ServiceProviderInterface;
}
