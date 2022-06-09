<?php

declare(strict_types=1);

namespace Ep\Base;

namespace Ep\Tests\App\Component;

use Ep\Base\Config;
use Yiisoft\Di\ServiceProviderInterface;

final class ServiceProvider implements ServiceProviderInterface
{
    private array $params;

    public function __construct(private Config $config)
    {
        $this->params = $config->params;
    }

    public function getDefinitions(): array
    {
        return [];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
