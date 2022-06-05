<?php

declare(strict_types=1);

namespace Ep\Tests\App\Traits;

use Ep\Attribute\Inject;
use Ep\Tests\App\Objects\Human\Father;

trait TestTrait
{
    #[Inject]
    private Father $father;

    private function print(string $text)
    {
        t($text);
    }
}
