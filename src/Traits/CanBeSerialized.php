<?php

declare(strict_types=1);

namespace Moccalotto\Stringy\Traits;

use Exception;
use OutOfRangeException;
use InvalidArgumentException;
use Moccalotto\Stringy\Stringy;

/**
 * Add serialization support for class
 */
trait CanBeSerialized
{
    /**
     * Serialize this object into a string.
     *
     * @see http://php.net/manual/class.serializable.php
     *
     * @return string
     */
    public function serialize()
    {
        return $this->string;
    }

    /**
     * Serialize this object into a json string.
     *
     * @see http://php.net/manual/class.serializable.php
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->string;
    }

    /**
     * Wake this object up after serialization.
     *
     * @see http://php.net/manual/class.serializable.php
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->string = $data;
    }

    /**
     * Support for unserialization of Stirngies exported via var_export
     *
     * @see http://php.net/manual/language.oop5.magic.php#object.set-state
     *
     * @param array $state
     *
     * @return Stringy
     */
    public static function __set_state(array $state)
    {
        return new static($state['string'], 'UTF-8');
    }
}
