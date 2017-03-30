<?php

/*
 * This file is part of the Stringy package.
 *
 * @package Stringy
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */
declare(strict_types=1);

namespace Moccalotto\Stringy\Traits;

/**
 * Add serialization support for class.
 */
trait HasToStringMethod
{
    /**
     * Get the content string encoded as the system's default encoding.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string();
    }
}
