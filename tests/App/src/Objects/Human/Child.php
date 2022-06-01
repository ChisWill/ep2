<?php

declare(strict_types=1);

namespace Ep\Tests\App\Objects\Human;

use Ep\Attribute\Inject;
use Ep\Tests\App\Objects\Weapon\Gun;

final class Child extends Father
{
    #[Inject]
    private Gun $gun;

    private string $name = 'Child';

    private function getWeapon(): string
    {
        return get_class($this->gun);
    }

    public function do(): string
    {
        return $this->name . '-' . $this->getWeapon() . '<br>' . $this->fight();
    }
}
