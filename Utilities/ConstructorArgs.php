<?php

namespace AppBundle\Utilities;

use Doctrine\Common\Util\Inflector;
use InvalidArgumentException;
use Traversable;

trait ConstructorArgs
{
    /**
     * @param array $args
     * @param bool $strictlyEnforced
     */
    protected function handleArgs(array $args = [], $strictlyEnforced = true)
    {
        foreach ($args as $k => $v) {
            $found = false;
            $method = 'set'.ucfirst($k);

            if (method_exists($this, $method)) {
                $found = true;
                $this->$method($v);
            } elseif(method_exists($this, Inflector::camelize($method))) {
                $found = true;
                $method = Inflector::camelize($method);
                $this->$method($v);
            } else {
                if (is_array($v) or $v instanceof Traversable) {
                    $method = 'add'.ucfirst($k);
                    if (method_exists($this, $method)) {
                        $found = true;
                        foreach ($v as $item) {
                            $this->$method($item);
                        }
                    }
                }
            }

            if (!$found && $strictlyEnforced) {
                throw new InvalidArgumentException(sprintf('No setter or adder found for "%s" in %s', $k, static::class));
            }
        }
    }
}