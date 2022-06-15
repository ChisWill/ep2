<?php

declare(strict_types=1);

namespace Ep\Tests\App\Annotation;

use Attribute;
use Ep\Attribute\Contract\AspectInterface;
use Ep\Base\Contract\HandlerInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final class TestAspect1 implements AspectInterface
{
	public function __construct(
		private string $name
	) {
	}

	public function handle(HandlerInterface $handler): mixed
	{
		t($this->name);

		$result = $handler->handle();

		t($this->name);

		return $result;
	}
}
