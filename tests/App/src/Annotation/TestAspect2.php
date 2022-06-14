<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;
use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\HandlerInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final class TestAspect2 implements AspectInterface
{
	public function __construct(
		private string $name
	) {
	}

	public function handle(HandlerInterface $handler): mixed
	{
		t($this->name);

		$result = $handler->handle();

		tt($this->name);

		return $result;
	}
}
