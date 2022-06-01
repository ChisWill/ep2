<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Attribute\Inject;

final class DemoService
{
    #[Inject]
    private TestService $testService;

    public function getAttr(): array
    {
        return $this->testService->getAttr();
    }
}
