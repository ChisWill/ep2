<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;
use Ep\Contract\Attribute\ProcessInterface;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
final class ClassAttribute implements ProcessInterface
{
	public function __construct(
		private string $name,
	) {
	}

	public function process(object $instance, Reflector $reflector, array $arguments = []): void
	{
	}
}
