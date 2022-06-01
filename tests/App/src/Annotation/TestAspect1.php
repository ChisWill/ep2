<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;
use Ep\Contract\Attribute\AspectInterface;
use Ep\Contract\HandlerInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final class TestAspect1 implements AspectInterface
{
	public function __construct(
		private string $name
	) {
	}

	public function handle(HandlerInterface $handler): mixed
	{
		tt($this->name);

		$result = $handler->handle();

		return $result;
	}
}
