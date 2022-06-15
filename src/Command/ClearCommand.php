<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Base\Config;
use Ep\Console\Contract\ConsoleResponseInterface;
use Ep\Helper\File;
use Ep\Console\Trait\ConsoleService;
use Yiisoft\Aliases\Aliases;

final class ClearCommand
{
    use ConsoleService;

    public function __construct(private Config $config)
    {
        $this->define('index')->setDescription('Clear runtime cache');
    }

    public function index(Aliases $aliases): ConsoleResponseInterface
    {
        $runtimeDir = $aliases->get($this->config->runtimeDir);
        File::rmdir($runtimeDir);
        File::mkdir($runtimeDir);

        return $this->success();
    }
}
