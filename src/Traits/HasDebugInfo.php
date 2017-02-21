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
 * Add serialization support for class
 */
trait HasDebugInfo
{
    /**
     * Get the debug info of the string.
     *
     * Useful for PSY shell debugging, var_dump, etc
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'string' => $this->string(),
            'length' => $this->length(),
            'size' => $this->size(),
        ];
    }
}
