<?php

declare(strict_types=1);

namespace Ep\Attribute;

use Ep\Contract\Attribute\ConfigureInterface;
use Yiisoft\Http\Method;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route implements ConfigureInterface
{
    public function __construct(
        private string $path,
        private string $method = Method::GET
    ) {
    }

    public function getValues(): array
    {
        return [
            'path' => $this->path,
            'method' => $this->method
        ];
    }
}
