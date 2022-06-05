<?php

declare(strict_types=1);

namespace Ep\Tests\App\Objects\Human;

use Ep\Attribute\Inject;
use Ep\Tests\App\Objects\Weapon\Bow;

class GrandPa
{
    #[Inject]
    private Bow $bow;

    protected string $name = 'GrandPa';

    private function getWeapon(): string
    {
        return get_class($this->bow);
    }

    public function shoot(): string
    {
        return $this->name . '-' . $this->getWeapon() . '<br>';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
