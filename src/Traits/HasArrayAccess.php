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
     * @throws OutOfRangeException      if $offset is larget than the length of the string
     */
    protected function ensureOffsetOk($offset)
    {
        if (filter_var($offset, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Array offset must be an integer');
        }

        if (abs($offset) >= $this->length()) {
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
     * @throws InvalidArgumentException if called
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetSet($offset, $value)
    {
        throw new InvalidArgumentException(sprintf(
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
     * @throws InvalidArgumentException if called
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function offsetUnset($offset)
    {
        throw new InvalidArgumentException(sprintf(
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
     * @throws OutOfRangeException      if $offset is larget than the length of the string
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
