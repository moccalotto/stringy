<?php

declare(strict_types=1);

namespace Moccalotto\Stringy\Traits;

use Exception;
use OutOfRangeException;
use InvalidArgumentException;
use Moccalotto\Stringy\Stringy;

/**
 * Adds array access to Stringy.
 */
trait HasArrayAccess
{
    /**
     * Check if an offset is legal.
     *
     * @param int $offset
     *
     * @throws InvalidArgumentException if $offset is not an integer
     * @throws OutOfRangeException      if $offset is < 0
     * @throws InvalidArgumentException if $offset is larget than the length of the string
     */
    protected function ensureOffsetOk($offset)
    {
        if ($offset != intval($offset)) {
            throw new InvalidArgumentException('Invalid non-integer offset');
        }

        if ($offset < 0) {
            throw new OutOfRangeException('Offset must be >= 0');
        }

        if ($offset >= $this->length()) {
            throw new OutOfRangeException(sprintf(
                'Illegal offset "%d". Must be lower than length of string: %d',
                $offset,
                $this->length()
            ));
        }
    }

    /**
     * Set character/value in string.
     *
     * Now allowed.
     *
     * @param mixed $offsetSet
     * @param mixed $value
     *
     * @throws StringyException if called
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetSet($offset, $value)
    {
        throw new StringyException(sprintf(
            'Trying to set $this[%s] to %s. Object mutation not allowed',
            $offset,
            $value
        ));
    }

    /**
     * Unset character/value in string.
     *
     * Now allowed.
     *
     * @param mixed $offsetSet
     *
     * @throws StringyException if called
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetUnset($offset)
    {
        throw new StringyException(sprintf(
            'Trying to unset [%s]. Object mutation not allowed',
            $offset
        ));
    }

    /**
     * Check hhether an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        try {
            $this->ensureOffsetOk($offset);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets a character from the string.
     *
     * @param int $offset The index/position of the character
     *
     * @return Stringy The character encoded as a Stringy
     *
     * @throws InvalidArgumentException if $offset is not an integer
     * @throws OutOfRangeException      if $offset is < 0
     * @throws InvalidArgumentException if $offset is larget than the length of the string
     */
    public function offsetGet($offset)
    {
        $this->ensureOffsetOk($offset);

        return $this->substring($offset, 1);
    }

    /**
     * Get the number of characters in the content string.
     *
     * @return int
     */
    public function count()
    {
        return $this->length();
    }
}
