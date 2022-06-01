<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class MethodAttribute
{
	public function __construct(
		private string $name,
		private int $age,
		private array $params
	) {
	}
}
