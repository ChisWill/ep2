<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep;
use InvalidArgumentException;
use ReflectionClass;

trait ConfigurableTrait
{
    public function configure(array $properties): static
    {
        if (Ep::getConfig()->debug) {
            foreach ($properties as $name => $value) {
                if ((new ReflectionClass($this))->getProperty($name)->isPrivate()) {
                    throw new InvalidArgumentException("The property \"{$name}\" is private.");
                }
                if (!property_exists($this, $name)) {
                    throw new InvalidArgumentException("The property \"{$name}\" is not exists.");
                }
                $this->$name = $value;
            }
        } else {
            foreach ($properties as $name => $value) {
                $this->$name = $value;
            }
        }

        return $this;
    }

    public function clone(array $properties): static
    {
        $new = clone $this;

        return $new->configure($properties);
    }
}
