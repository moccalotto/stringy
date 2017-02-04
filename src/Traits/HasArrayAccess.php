<?php

namespace Moccalotto\Stringy\Traits;

use OutOfRangeException;
use OutOfBoundsException;

trait HasArrayAccess
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetSet($offset, $value)
    {
        throw new StringyException('Direct object modification not allowed');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetUnset($offset)
    {
        throw new StringyException('Direct object modification not allowed');
    }

    public function offsetExists($offset)
    {
        if ($offset != intval($offset)) {
            return false;
        }

        $offset = (int) $offset;

        if ($offset < 0) {
            return false;
        }

        if ($offset >= $this->length()) {
            return false;
        }

        return true;
    }

    public function offsetGet($offset)
    {
        if ($offset != intval($offset)) {
            throw new OutOfBoundsException('Invalid non-integer offset');
        }

        if ($offset < 0) {
            throw new OutOfRangeException('Offset must be >= 0');
        }

        if ($offset >= $this->length()) {
            throw new OutOfRangeException(sprintf('Offset must be < %d', $this->length()));
        }

        return $this->characters()[(int) $offset];
    }
}
