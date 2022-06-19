<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Attribute\Inject;
use Ep\Web\Service;

final class TestService
{
    #[Inject]
    private Service $service;

    public function index(): void
    {
    }
}
