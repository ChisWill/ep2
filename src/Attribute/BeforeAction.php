<?php

declare(strict_types=1);

namespace Ep\Attribute;

use Ep\Contract\Attribute\ConfigureInterface;
use Attribute;
use LogicException;

#[Attribute(Attribute::TARGET_METHOD)]
final class BeforeAction implements ConfigureInterface
{
    public function __construct(
        mixed ...$value
    ) {
        if ($value) {
            throw new LogicException('Do not pass parameters for ' . BeforeAction::class);
        }
    }

    public function getValues(): array
    {
        return [];
    }
}
